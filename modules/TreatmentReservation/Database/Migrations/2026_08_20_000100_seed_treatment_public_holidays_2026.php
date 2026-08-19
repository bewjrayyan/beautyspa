<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'date' => '2026-01-01',
                'name' => 'Tahun Baharu',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-01-14',
                'name' => 'Hari Keputeraan Yang di-Pertuan Besar Negeri Sembilan',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'NSN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-01-17',
                'name' => 'Israk dan Mikraj',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'KDH',
                    'NSN',
                    'PLS',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-01',
                'name' => 'Hari Thaipusam',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KUL',
                    'NSN',
                    'PJY',
                    'PNG',
                    'PRK',
                    'SGR'
                ],
                'is_subject_to_change' => false,
                'color' => '#0ea5e9',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-01',
                'name' => 'Hari Wilayah Persekutuan',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'KUL',
                    'LBN',
                    'PJY'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-17',
                'name' => 'Tahun Baharu Cina',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#ef4444',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-18',
                'name' => 'Tahun Baharu Cina (Hari Kedua)',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#ef4444',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-19',
                'name' => 'Awal Ramadan',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'JHR',
                    'KDH'
                ],
                'is_subject_to_change' => true,
                'color' => '#475569',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-20',
                'name' => 'Hari Pengisytiharan Tarikh Kemerdekaan',
                'day_name' => 'Friday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-04',
                'name' => 'Hari Ulang Tahun Pertabalan Sultan Terengganu',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-07',
                'name' => 'Hari Nuzul Al-Quran',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'KTN',
                    'KUL',
                    'LBN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SGR',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-21',
                'name' => 'Hari Raya Puasa',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-22',
                'name' => 'Hari Raya Puasa (Hari Kedua)',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-23',
                'name' => 'Hari Keputeraan Sultan Johor',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-23',
                'name' => 'Hari Raya Puasa (Hari Ketiga)',
                'day_name' => 'Monday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-30',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Sabah',
                'day_name' => 'Monday',
                'state_codes' => [
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-04-03',
                'name' => 'Good Friday',
                'day_name' => 'Friday',
                'state_codes' => [
                    'SBH',
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#64748b',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-04-26',
                'name' => 'Hari Keputeraan Sultan Terengganu',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-01',
                'name' => 'Hari Pekerja',
                'day_name' => 'Friday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#f97316',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-17',
                'name' => 'Hari Ulang Tahun Keputeraan Raja Perlis',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'PLS'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-22',
                'name' => 'Hari Hol Almarhum Sultan Ahmad Shah Al-Musta\'in Billah ibni Almarhum Sultan Abu Bakar',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PHG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-26',
                'name' => 'Hari Arafah',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'KTN',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-27',
                'name' => 'Hari Raya Qurban',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-28',
                'name' => 'Hari Raya Qurban (Hari Kedua)',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'KDH',
                    'KTN',
                    'PLS',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-30',
                'name' => 'Pesta Kaamatan (Pesta Menuai)',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'LBN',
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-31',
                'name' => 'Hari Wesak',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-31',
                'name' => 'Pesta Kaamatan (Pesta Menuai)',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'LBN',
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-01',
                'name' => 'Hari Keputeraan Rasmi Seri Paduka Baginda Yang di-Pertuan Agong',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-01',
                'name' => 'Perayaan Hari Gawai Dayak',
                'day_name' => 'Monday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-02',
                'name' => 'Perayaan Hari Gawai Dayak',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-17',
                'name' => 'Awal Muharam (Maal Hijrah)',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-21',
                'name' => 'Hari Keputeraan Sultan Kedah',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'KDH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-07',
                'name' => 'Hari Ulang Tahun Perisytiharan Tapak Warisan Dunia',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'PNG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-11',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Pulau Pinang',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'PNG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-21',
                'name' => 'Hari Hol Almarhum Sultan Iskandar',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-22',
                'name' => 'Hari Kemerdekaan Sarawak',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-31',
                'name' => 'Hari Keputeraan Sultan Pahang',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PHG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-24',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Melaka',
                'day_name' => 'Monday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-25',
                'name' => 'Hari Keputeraan Nabi Muhammad S.A.W. (Maulidur Rasul)',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#475569',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-31',
                'name' => 'Hari Kebangsaan',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-16',
                'name' => 'Hari Malaysia',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-29',
                'name' => 'Hari Keputeraan Sultan Kelantan',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'KTN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-30',
                'name' => 'Hari Keputeraan Sultan Kelantan',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'KTN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-10-09',
                'name' => 'Bos Pergi umrah',
                'day_name' => null,
                'state_codes' => [
                    'KUL'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-10-10',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Sarawak',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-11-06',
                'name' => 'Hari Keputeraan Sultan Perak',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PRK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-11-08',
                'name' => 'Hari Deepavali',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#f59e0b',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-11',
                'name' => 'Hari Keputeraan Sultan Selangor',
                'day_name' => 'Friday',
                'state_codes' => [
                    'SGR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-24',
                'name' => 'Christmas Eve',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#16a34a',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-25',
                'name' => 'Hari Krismas',
                'day_name' => 'Friday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#16a34a',
                'source' => 'malaysia-holiday-api:v1'
            ]
        ];

        foreach ($rows as $row) {
            DB::table('treatment_public_holidays')->updateOrInsert(
                [
                    'date' => $row['date'],
                    'name' => $row['name'],
                    'source' => $row['source'],
                ],
                [
                    'day_name' => $row['day_name'],
                    'state_codes' => json_encode($row['state_codes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_subject_to_change' => $row['is_subject_to_change'],
                    'color' => $row['color'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $rows = [
            [
                'date' => '2026-01-01',
                'name' => 'Tahun Baharu',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-01-14',
                'name' => 'Hari Keputeraan Yang di-Pertuan Besar Negeri Sembilan',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'NSN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-01-17',
                'name' => 'Israk dan Mikraj',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'KDH',
                    'NSN',
                    'PLS',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-01',
                'name' => 'Hari Thaipusam',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KUL',
                    'NSN',
                    'PJY',
                    'PNG',
                    'PRK',
                    'SGR'
                ],
                'is_subject_to_change' => false,
                'color' => '#0ea5e9',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-01',
                'name' => 'Hari Wilayah Persekutuan',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'KUL',
                    'LBN',
                    'PJY'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-17',
                'name' => 'Tahun Baharu Cina',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#ef4444',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-18',
                'name' => 'Tahun Baharu Cina (Hari Kedua)',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#ef4444',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-19',
                'name' => 'Awal Ramadan',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'JHR',
                    'KDH'
                ],
                'is_subject_to_change' => true,
                'color' => '#475569',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-02-20',
                'name' => 'Hari Pengisytiharan Tarikh Kemerdekaan',
                'day_name' => 'Friday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-04',
                'name' => 'Hari Ulang Tahun Pertabalan Sultan Terengganu',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-07',
                'name' => 'Hari Nuzul Al-Quran',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'KTN',
                    'KUL',
                    'LBN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SGR',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-21',
                'name' => 'Hari Raya Puasa',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-22',
                'name' => 'Hari Raya Puasa (Hari Kedua)',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-23',
                'name' => 'Hari Keputeraan Sultan Johor',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-23',
                'name' => 'Hari Raya Puasa (Hari Ketiga)',
                'day_name' => 'Monday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-03-30',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Sabah',
                'day_name' => 'Monday',
                'state_codes' => [
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-04-03',
                'name' => 'Good Friday',
                'day_name' => 'Friday',
                'state_codes' => [
                    'SBH',
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#64748b',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-04-26',
                'name' => 'Hari Keputeraan Sultan Terengganu',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-01',
                'name' => 'Hari Pekerja',
                'day_name' => 'Friday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#f97316',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-17',
                'name' => 'Hari Ulang Tahun Keputeraan Raja Perlis',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'PLS'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-22',
                'name' => 'Hari Hol Almarhum Sultan Ahmad Shah Al-Musta\'in Billah ibni Almarhum Sultan Abu Bakar',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PHG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-26',
                'name' => 'Hari Arafah',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'KTN',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-27',
                'name' => 'Hari Raya Qurban',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-28',
                'name' => 'Hari Raya Qurban (Hari Kedua)',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'KDH',
                    'KTN',
                    'PLS',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-30',
                'name' => 'Pesta Kaamatan (Pesta Menuai)',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'LBN',
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-31',
                'name' => 'Hari Wesak',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-05-31',
                'name' => 'Pesta Kaamatan (Pesta Menuai)',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'LBN',
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-01',
                'name' => 'Hari Keputeraan Rasmi Seri Paduka Baginda Yang di-Pertuan Agong',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-01',
                'name' => 'Perayaan Hari Gawai Dayak',
                'day_name' => 'Monday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-02',
                'name' => 'Perayaan Hari Gawai Dayak',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#7c3aed',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-17',
                'name' => 'Awal Muharam (Maal Hijrah)',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-06-21',
                'name' => 'Hari Keputeraan Sultan Kedah',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'KDH'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-07',
                'name' => 'Hari Ulang Tahun Perisytiharan Tapak Warisan Dunia',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'PNG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-11',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Pulau Pinang',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'PNG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-21',
                'name' => 'Hari Hol Almarhum Sultan Iskandar',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-22',
                'name' => 'Hari Kemerdekaan Sarawak',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-07-31',
                'name' => 'Hari Keputeraan Sultan Pahang',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PHG'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-24',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Melaka',
                'day_name' => 'Monday',
                'state_codes' => [
                    'MLK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-25',
                'name' => 'Hari Keputeraan Nabi Muhammad S.A.W. (Maulidur Rasul)',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#475569',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-08-31',
                'name' => 'Hari Kebangsaan',
                'day_name' => 'Monday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-16',
                'name' => 'Hari Malaysia',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#dc2626',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-29',
                'name' => 'Hari Keputeraan Sultan Kelantan',
                'day_name' => 'Tuesday',
                'state_codes' => [
                    'KTN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-09-30',
                'name' => 'Hari Keputeraan Sultan Kelantan',
                'day_name' => 'Wednesday',
                'state_codes' => [
                    'KTN'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-10-09',
                'name' => 'Bos Pergi umrah',
                'day_name' => null,
                'state_codes' => [
                    'KUL'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-10-10',
                'name' => 'Hari Jadi Yang di-Pertua Negeri Sarawak',
                'day_name' => 'Saturday',
                'state_codes' => [
                    'SWK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-11-06',
                'name' => 'Hari Keputeraan Sultan Perak',
                'day_name' => 'Friday',
                'state_codes' => [
                    'PRK'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-11-08',
                'name' => 'Hari Deepavali',
                'day_name' => 'Sunday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'TRG'
                ],
                'is_subject_to_change' => true,
                'color' => '#f59e0b',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-11',
                'name' => 'Hari Keputeraan Sultan Selangor',
                'day_name' => 'Friday',
                'state_codes' => [
                    'SGR'
                ],
                'is_subject_to_change' => false,
                'color' => '#3b82f6',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-24',
                'name' => 'Christmas Eve',
                'day_name' => 'Thursday',
                'state_codes' => [
                    'SBH'
                ],
                'is_subject_to_change' => false,
                'color' => '#16a34a',
                'source' => 'malaysia-holiday-api:v1'
            ],
            [
                'date' => '2026-12-25',
                'name' => 'Hari Krismas',
                'day_name' => 'Friday',
                'state_codes' => [
                    'JHR',
                    'KDH',
                    'KTN',
                    'KUL',
                    'LBN',
                    'MLK',
                    'NSN',
                    'PHG',
                    'PJY',
                    'PLS',
                    'PNG',
                    'PRK',
                    'SBH',
                    'SGR',
                    'SWK',
                    'TRG'
                ],
                'is_subject_to_change' => false,
                'color' => '#16a34a',
                'source' => 'malaysia-holiday-api:v1'
            ]
        ];

        foreach ($rows as $row) {
            DB::table('treatment_public_holidays')
                ->where('date', $row['date'])
                ->where('name', $row['name'])
                ->where('source', $row['source'])
                ->delete();
        }
    }
};
