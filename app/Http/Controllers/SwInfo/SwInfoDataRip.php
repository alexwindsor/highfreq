<?php

namespace App\Http\Controllers\SwInfo;

use App\Models\SwInfoBroadcast;
use App\Models\Station;
use App\Models\Language;
use App\Models\SwInfoTransmitter;


class SwInfoDataRip
{

    public static function shortWaveInfoGet() {

        // exit if the date on short-wave.info is the same as the date we have on the server
        $date_on_server = self::getServerDateOfData();
        $date_on_swinfo = self::getSwInfoDateOfData();
        if (intval($date_on_swinfo) <= intval($date_on_server)) return 'already got most recent data - no scrape performed';

        SwInfoBroadcast::truncate();

        // loop through the stations insert new ones into the database and parse each page of data
        self::parseStationPageData();

        // save the date of the latest import into the file
        file_put_contents('swinfo/date', $date_on_swinfo);

        // delete any stations or languages that have been orphaned (and aren't linked to any user logs)
        // self::killOrphans(); // don't need to do this just yet as there are no orphans, it seems

        return 'completed';

    }


    public static function getServerDateOfData() {
        return file_get_contents('swinfo/date');
    }


    private static function getSwInfoDateOfData() {

        sleep(3);
        $page = file_get_contents('https://www.short-wave.info');
        $page = substr($page, strpos($page, 'Database dated ') + strlen('Database dated '));
        $date = substr($page, 0, strpos($page, '.'));

        if (! strpos($page, 'Database dated ') > 1) return false;

        $month = preg_replace('/[^a-zA-Z]/', '', $date);
        $dateArray = date_parse($month);
        preg_match("/^\d+/", $date, $day);
        preg_match("/\d+$/", $date, $year);

        $date = '20' . $year[0] . str_pad($dateArray['month'], 2, '0', STR_PAD_LEFT) . $day[0];

        return $date;
    }




    private static function parseStationPageData() {

        sleep(3); // gap between requests to short-wave.info

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.short-wave.info/index.php?station=*');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $page = curl_exec($ch);
        curl_close($ch);

        $start_of_cut = '<tr id="rowZ">';

        $page = substr($page, strpos($page, $start_of_cut) + strlen($start_of_cut));
        $page = substr($page, strpos($page, "\n"), strpos($page, '</tbody>') - strpos($page, "\n"));

        $html_rows = explode("\n", $page);
        $rows = [];

        $rowcount = 0;

        foreach($html_rows as $row) {

            if (! $row) continue;

            // get freq
            preg_match_all('/[0-9]{4,5}&#8201;/i', $row, $freq);
            if (! isset($freq[0][0])) continue;
            $rows[$rowcount]['freq'] = trim($freq[0][0]);

            // get station name
            $row = substr($row, strpos($row, '&#8201;</a></td><td>') + strlen('&#8201;</a></td><td>'));
            $rows[$rowcount]['station'] = substr($row, strpos($row, '>') + 1, strpos($row, '</a>') - strpos($row, '>') - 1);
            $rows[$rowcount]['station'] = trim(html_entity_decode($rows[$rowcount]['station']));
            
            // get start
            $row = substr($row, strpos($row, '</td><td>') + strlen('</td><td>'));
            $rows[$rowcount]['start'] = substr($row, 0, 5);

            // get end
            $row = substr($row, strpos($row, '</td><td>') + strlen('</td><td>'));
            $rows[$rowcount]['end'] = substr($row, 0, 5);

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

            // get transmitterlat
            $row = substr($row, strpos($row, 'Latitude: ') + strlen('Latitude: '));
            $rows[$rowcount]['transmitterlat'] = substr($row, 0, strpos($row, '<br'));
            $rows[$rowcount]['transmitterlat'] = html_entity_decode(trim($rows[$rowcount]['transmitterlat']));

            // get transmitterlong
            $row = substr($row, strpos($row, 'Longitude: ') + strlen('Longitude: '));
            $rows[$rowcount]['transmitterlong'] = substr($row, 0, strpos($row, '</'));
            $rows[$rowcount]['transmitterlong'] = html_entity_decode(trim($rows[$rowcount]['transmitterlong']));
  
            $rowcount++;
        }

        // echo '<pre>';
        // print_r($rows);
        // echo '</pre>';

        // insert the rows into the database
        foreach ($rows as $row) {

            $station = Station::firstOrCreate(['name' => $row['station'], 'station_types_id' => 1]);
            $language = Language::firstOrCreate(['name' => $row['language']]);
            $sw_info_transmitters = SwInfoTransmitter::firstOrCreate(['name' => $row['transmittersite']], ['longitude' => $row['transmitterlong'], 'latitude' => $row['transmitterlat']]);

            SwInfoBroadcast::create([
                'station_id' => $station->id,
                'language_id' => $language->id,
                'sw_info_transmitter_id' => $sw_info_transmitters->id,
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


    // private static function killOrphans() {

    //     SELECT * FROM `languages` where id not in (select language_id from sw_info_broadcasts) and id not in (select language_id from logs); 
    //     SELECT * FROM `stations` where id not in (select station_id from sw_info_broadcasts) and id not in (select station_id from logs); 

    // }








}
