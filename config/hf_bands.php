<?php

return [
    'low_frequency' => [30, 300],
    'medium_frequency' => [300, 3000],
    'high_frequency' => [3000, 30000],
    'longwave' => [148.5, 283.5],
    'mediumwave' => [531, 1701],
    'shortwave' => [3000, 30000],
    'broadcast_bands' => [
        [2300, 2495, '120m'],
        [3200, 3400, '90m'],
        [3900, 4000, '75m'],
        [4750, 5060, '60m'],
        [5900, 6200, '49m'],
        [7200, 7600, '41m'],
        [9400, 9900, '31m'],
        [11600, 12100, '25m'],
        [13570, 13870, '22m'],
        [15100, 15800, '19m'],
        [17480, 17900, '16m'],
        [18900, 19020, '15m'],
        [21450, 21850, '13m'],
        [25600, 26100, '11m']
    ],
    'amateur_bands' => [
        [135.7, 137.8, '2200m'],
        [472, 479, '630m'],
        [1800, 2000, '160m'],
        [3500, 4000, '80m'],
        [5351, 5367, '60m'],
        [7000, 7300, '40m'],
        [10100, 10150, '30m'],
        [14000, 14350, '20m'],
        [18068, 18168, '17m'],
        [21000, 21450, '15m'],
        [24890, 24990, '12m'],
        [28000, 29700, '10m'],
    ],
    'aeronautical_bands' => [
        [2850, 3155],
        [3400, 3500],
        [3800, 3950],
        [4650, 4750],
        [5450, 5730],
        [6525, 6765],
        [8815, 9040],
        [10005, 10100],
        [11175, 11400],
        [13200, 13360],
        [15010, 15100],
        [17900, 18030],
        [21870, 22000],
        [23200, 23350],
    ],
    'civil_aeronautical_bands' => [
        [2850, 3025],
        [3400, 3500],
        [3800, 3950],
        [4650, 4700],
        [5450, 5680],
        [6525, 6685],
        [8815, 8965],
        [10005, 10100],
        [11175, 11275],
        [13200, 13260],
        [15010, 15100],
        [17900, 17970],
        [21870, 22000],
        [23200, 23350],
    ],
    'military_aeronautical_bands' => [
        [3025, 3155],
        [4700, 4750],
        [5680, 5730],
        [6685, 6765],
        [8965, 9040],
        [11275, 11400],
        [13260, 13360],
        [17970, 18030],
    ],
];
