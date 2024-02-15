<?php /** @noinspection ALL */

namespace App\Http\Controllers\SwInfo;

use Illuminate\Support\Facades\DB;
use App\Models\SwInfoBroadcast;
use App\Models\Station;
use App\Models\StationProgramme;
use App\Models\Language;
use App\Models\Transmitter;

class SwInfoDataRip
{
    // filter out non-broadcast stations
    private static $blocked_stations = [
        'numbers station 1',
        'numbers station 2',
        'north korean jamming',
        'slot machine',
        'caribbean beacon',
        'cuban spy numers',
        'chu canada',
        'jmh',
        'ssb meteorogical broadcasting',
        'wwv',
        'wwvh',
        'jmh',
        'jmh',
        'hfd radio station',
        'hll seoul meteorological radio',
        'caribbean beacon (university netw',
    ];

    // prefixes that are removed and added later so they are not considered to be radio stations
    private static $prefixes = [
        'RADIO CHANNEL',
        'RADIO FREE',
        'RADIO',
        'VOICE OF THE',
        'VOICE OF',
        'CHINA',
        'ECHO OF',
        'VOA',
        'WORLD',
    ];




    public static function shortWaveInfoGet() {

        // exit if the date on short-wave.info is the same as the date we have on the server
        $date_on_server = self::getServerDateOfData();
        $date_on_swinfo = self::getSwInfoDateOfData();

        $date_message = 'Data on server = ' . $date_on_server . ' and data on short-wave.info = ' . $date_on_swinfo;


        if (intval($date_on_swinfo) <= intval($date_on_server)) return $date_message . '<br><br>No scrape performed...';

        // truncate the broadcasts table
        SwInfoBroadcast::truncate();

        // loop through the stations insert new ones into the database and parse each page of data
        self::parseStationPageData();

        // save the date of the latest import into the file
        file_put_contents('swinfo/date', $date_on_swinfo);

        // delete any stations or languages that have been orphaned (and aren't linked to any user logs)
        self::killOrphans();

        return $date_message . '<br><br>completed';
    }


    public static function getServerDateOfData() {
        return file_get_contents('swinfo/date');
        // return 0;
    }


    private static function getSwInfoDateOfData() {

        sleep(3); // so the website doesn't think that we are a scraper bot (which we are!)
        $page = file_get_contents('https://www.short-wave.info');
        $page = substr($page, strpos($page, 'Database dated ') + strlen('Database dated '));
        $date = substr($page, 0, strpos($page, '.'));
        if (! strpos($page, 'Database dated ') > 1) return false;

        $dateArray = date_parse($date);
        if ($dateArray['warning_count'] !== 0) return false;
        $datestamp = $dateArray['year'] . str_pad($dateArray['month'], 2, 0, STR_PAD_LEFT) . str_pad($dateArray['day'], 2, 0, STR_PAD_LEFT);

        return $datestamp;
    }


    private static function parseStationPageData() {

        sleep(3); // gap between requests to short-wave.info

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.short-wave.info/index.php?station=*');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $page = curl_exec($ch);
        curl_close($ch);

        // $page = file_get_contents('swinfo/short-wave_info');

        $start_of_cut = '<tr id="rowZ">';

        $page = substr($page, strpos($page, $start_of_cut) + strlen($start_of_cut));
        $page = substr($page, strpos($page, "\n"), strpos($page, '</tbody>') - strpos($page, "\n"));

        $html_rows = explode("\n", $page);
        $rows = [];

        $rowcount = 0;

        // loop through all the broadcasts
        foreach($html_rows as $row) {

            if (! $row) continue;

            // get freq
            preg_match_all('/[0-9]{4,5}&#8201;/i', $row, $freq);

            if (! isset($freq[0][0])) continue;
            $rows[$rowcount]['freq'] = trim($freq[0][0]);

            // get station name
            $row = substr($row, strpos($row, '</td><td>') + strlen('&#8201;</a></td><td>'));
            $rows[$rowcount]['station'] = substr($row, strpos($row, '>') + 1, strpos($row, '</td><td>') - strpos($row, '>') - 1);
            $rows[$rowcount]['station'] = strip_tags($rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = html_entity_decode($rows[$rowcount]['station']);
            if ($rows[$rowcount]['station'] === '') continue;
            $rows[$rowcount]['station'] = strtoupper($rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('R.', 'RADIO ', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('VO ', 'VOICE OF ', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('INT.', 'INTERNATIONAL', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('ISLAMIC REP.IRAN', 'IRAN', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('/', ' / ', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = str_replace('  ', ' ', $rows[$rowcount]['station']);
            $rows[$rowcount]['station'] = trim($rows[$rowcount]['station']);
            if ($rows[$rowcount]['station'] === 'CHINA RADIO INTERNATIONA') $rows[$rowcount]['station'] = 'CHINA RADIO INTERNATIONAL';
            if ($rows[$rowcount]['station'] === "'''VOICE OF CHINA'' RADIO STATION'") $rows[$rowcount]['station'] = 'VOICE OF CHINA RADIO STATION';

            // check the station against an array of stations that we are not interested in
            if (in_array(strtolower($rows[$rowcount]['station']), self::$blocked_stations)) continue;

            // get start
            $row = substr($row, strpos($row, '</td><td>') + strlen('</td><td>'));
            $rows[$rowcount]['start'] = substr($row, 0, 5);

            // get end
            $row = substr($row, strpos($row, '</td><td>') + strlen('</td><td>'));
            $rows[$rowcount]['end'] = substr($row, 0, 5);
            if ($rows[$rowcount]['end'] === '00:00') $rows[$rowcount]['end'] = '23:59';

            // get days
            $row = substr($row, strpos($row, '</td><td>') + strlen('</td><td>'));
            $rows[$rowcount]['days'] = substr($row, 0, strpos($row, '<'));
            $rows[$rowcount]['days'] = Weekdays::swInfoDaysToBitwise($rows[$rowcount]['days']);

            // get language
            $row = substr($row, strpos($row, '">') + strlen('">'));
            $rows[$rowcount]['language'] = substr($row, 0, strpos($row, '<'));

            // get power
            $row = substr($row, strpos($row, '<td>') + strlen('<td>'));
            $rows[$rowcount]['power'] = intval(trim(substr($row, 0, strpos($row, '<'))));

            // get azimuth
            $row = substr($row, strpos($row, '<td>') + strlen('<td>'));
            $rows[$rowcount]['azimuth'] = substr($row, 0, strpos($row, '<'));

            // get transmittersite
            $row = substr($row, strpos($row, 'txsite=') + strlen('txsite='));
            $rows[$rowcount]['transmittersite'] = substr($row, 0, strpos($row, '">'));
            $rows[$rowcount]['transmittersite'] = html_entity_decode(trim($rows[$rowcount]['transmittersite']));
            $rows[$rowcount]['transmittersite'] = urldecode($rows[$rowcount]['transmittersite']);
            if ($rows[$rowcount]['transmittersite'] === '') $rows[$rowcount]['transmittersite'] = 'Unknown';

            $rows[$rowcount]['transmitterlat'] = $rows[$rowcount]['transmitterlong'] = null;
            if (strpos($row, 'Latitude: ') > 1) {
                // get transmitterlat
                $row = substr($row, strpos($row, 'Latitude: ') + strlen('Latitude: '));
                $rows[$rowcount]['transmitterlat'] = substr($row, 0, strpos($row, '<br'));
                $rows[$rowcount]['transmitterlat'] = html_entity_decode(trim($rows[$rowcount]['transmitterlat']));

                // get transmitterlong
                $row = substr($row, strpos($row, 'Longitude: ') + strlen('Longitude: '));
                $rows[$rowcount]['transmitterlong'] = substr($row, 0, strpos($row, '</'));
                $rows[$rowcount]['transmitterlong'] = html_entity_decode(trim($rows[$rowcount]['transmitterlong']));
            }



            // null placeholder for the programme that will be extracted from the 'station' string below
            $rows[$rowcount]['programme'] = null;

            $rowcount++;
        }

        // sort the array by station alphabetically
        usort($rows, function($a, $b){ return strcmp($a["station"], $b["station"]); });


        $previous_station = [];

        // compare each word in each station with each word in the previous station to see if it is a programme
        for ($i = 0; $i < count($rows); $i++) {

            // check if the station has a prefix (from the hard coded list of prefixes) and remove that prefix, to be added later
            // otherwise it will think that 'voice of' or 'radio' is the name of a station
            $rows[$i]['station_prefix'] = null;

            foreach (self::$prefixes as $prefix) {
                if (str_starts_with($rows[$i]['station'], $prefix)) {
                    $rows[$i]['station_prefix'] = $prefix . ' ';
                    $rows[$i]['station'] = str_replace($rows[$i]['station_prefix'], '', $rows[$i]['station']);
                    break;
                }
            }

            // make an array of words that the station contains
            $this_station = explode(' ', $rows[$i]['station']);

            if ($this_station === $previous_station) continue;

            $station = '';
            $programme = '';

            // work out which has more words between this station and the previous
            $num_of_words = count($this_station) > count($previous_station) ? count($this_station) : count($previous_station);



            for ($j = 0; $j < $num_of_words; $j++) {

                if (!isset($this_station[$j])) $this_station[$j] = '';
                if (!isset($previous_station[$j])) $previous_station[$j] = '';

                // if both first words are different then we have a new station name and move on
                if ($j === 0 && $previous_station[$j] !== $this_station[$j]) {
                    $station = $rows[$i]['station'];
                    break;
                }

                // if they match and we haven't started making the programme, keep building the station name
                if (($previous_station[$j] === $this_station[$j]) && $programme === '') {
                    $station .= $this_station[$j] . ' ';
                }
                // if we have a divergence between the words then add it to the name of the programme
                elseif ($previous_station[$j] != $this_station[$j]) {
                    $programme .= $this_station[$j] . ' ';
                }
            }

            $rows[$i]['station'] = trim($station);
            $rows[$i]['programme'] = trim($programme);

            // if there is a programme, we need to check the previous station to see if a programme needs to be made out of it
            if (!empty($programme)) {

                $n = 1;

                while (strpos($rows[$i - $n]['station'], $rows[$i]['station']) === 0 && strlen($rows[$i - $n]['station']) > strlen($rows[$i]['station'])) {
                    $rows[$i - $n]['programme'] = trim(str_replace($rows[$i]['station'], '', $rows[$i - $n]['station'])) . ' ' . $rows[$i - $n]['programme'];
                    $rows[$i - $n]['station'] = $rows[$i]['station'];
                    $n++;
                }
            }

            $previous_station = trim(str_replace($rows[$i]['station_prefix'], '', $rows[$i]['station']));
            $previous_station = explode(' ', $previous_station);
        }


        // reapply the suffixes to the station name
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]['station_prefix']) $rows[$i]['station'] = $rows[$i]['station_prefix'] . $rows[$i]['station'];
            // echo out the rows for debugging:
            echo $rows[$i]['station'] . ' <span style="color:gray">' . $rows[$i]['programme'] . '</span><br>';
            // echo ' <span style="font-size:80%">' . $rows[$i]['language'] . '</span><br>';
        }

        // die();


        // dd($rows);

        // insert the rows into the database
        foreach ($rows as $row) {

            $station = Station::firstOrCreate(['station_type_id' => 1, 'name' => trim($row['station'])]);

            $station_programme_id = null;
            if (! empty($row['programme'])) {
                $programme = StationProgramme::firstOrCreate(['station_id' => $station->id, 'name' => trim($row['programme'])]);
                $station_programme_id = $programme->id;
            }

            $language_id = 1; // unknown or n/a
            if (! empty($row['language'])) {
                $language = Language::firstOrCreate([
                    'name' => $row['language'],
                    'station_type_id' => 1
                ]);
                $language_id = $language->id;
            }

            $transmitter = Transmitter::firstOrCreate(['name' => $row['transmittersite']], ['longitude' => $row['transmitterlong'], 'latitude' => $row['transmitterlat']]);

            SwInfoBroadcast::create([
                'station_id' => $station->id,
                'station_programme_id' => $station_programme_id,
                'language_id' => $language->id,
                'transmitter_id' => $transmitter->id,
                'frequency' => intval($row['freq']),
                'start_time' => trim($row['start']) . ':00',
                'end_time' => $row['end'] . ':00',
                'weekdays' => $row['days'],
                'strength' => $row['power'],
                'azimuth' => $row['azimuth']
            ]);

        }

        return true;

    }

    
    private static function killOrphans() {

        DB::statement('
            DELETE FROM `station_programmes` where
            station_id not in (select station_id from sw_info_broadcasts) and
            station_id not in (select station_id from logs) and
            id not in (select station_programme_id from logs where station_programme_id is not null)
        ');

        DB::statement('
            DELETE FROM `stations` where
            id not in (select station_id from sw_info_broadcasts) and
            id not in (select station_id from logs)
        ');

        DB::statement('
            DELETE FROM `languages` where
            id not in (select language_id from sw_info_broadcasts) and
            id not in (select language_id from logs) and id > 1
        ');

        DB::statement('
            DELETE FROM `transmitters` where id not in
            (select transmitter_id from sw_info_broadcasts)
        ');
    }



}
