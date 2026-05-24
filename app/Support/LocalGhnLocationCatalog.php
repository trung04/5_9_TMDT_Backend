<?php

namespace App\Support;

class LocalGhnLocationCatalog
{
    /**
     * @return list<array{ProvinceID:int, ProvinceName:string, Code:string, NameExtension:list<string>}>
     */
    public function provinces(): array
    {
        return collect(self::DATA)
            ->map(fn (array $province): array => [
                'ProvinceID' => $province['ProvinceID'],
                'ProvinceName' => $province['ProvinceName'],
                'Code' => $province['Code'],
                'NameExtension' => $province['NameExtension'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{DistrictID:int, ProvinceID:int, DistrictName:string, Code:string, Type:int, SupportType:int}>
     */
    public function districts(int $provinceId): array
    {
        $province = $this->findProvince($provinceId);

        if (! $province) {
            return [];
        }

        return collect($province['districts'])
            ->map(fn (array $district): array => [
                'DistrictID' => $district['DistrictID'],
                'ProvinceID' => $province['ProvinceID'],
                'DistrictName' => $district['DistrictName'],
                'Code' => $district['Code'],
                'Type' => 1,
                'SupportType' => 3,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{WardCode:string, DistrictID:int, WardName:string}>
     */
    public function wards(int $districtId): array
    {
        foreach (self::DATA as $province) {
            foreach ($province['districts'] as $district) {
                if ($district['DistrictID'] !== $districtId) {
                    continue;
                }

                return collect($district['wards'])
                    ->map(fn (array $ward): array => [
                        'WardCode' => $ward['WardCode'],
                        'DistrictID' => $district['DistrictID'],
                        'WardName' => $ward['WardName'],
                    ])
                    ->values()
                    ->all();
            }
        }

        return [];
    }

    private function findProvince(int $provinceId): ?array
    {
        foreach (self::DATA as $province) {
            if ($province['ProvinceID'] === $provinceId) {
                return $province;
            }
        }

        return null;
    }

    private const DATA = [
        [
            'ProvinceID' => 201,
            'ProvinceName' => 'Ha Noi',
            'Code' => 'HN',
            'NameExtension' => ['Ha Noi', 'Hanoi', 'Thu do'],
            'districts' => [
                [
                    'DistrictID' => 1450,
                    'DistrictName' => 'Ba Dinh',
                    'Code' => 'HN-BD',
                    'wards' => [
                        ['WardCode' => 'HN-BD-01', 'WardName' => 'Phuc Xa'],
                        ['WardCode' => 'HN-BD-02', 'WardName' => 'Truc Bach'],
                        ['WardCode' => 'HN-BD-03', 'WardName' => 'Ngoc Ha'],
                    ],
                ],
                [
                    'DistrictID' => 1454,
                    'DistrictName' => 'Cau Giay',
                    'Code' => 'HN-CG',
                    'wards' => [
                        ['WardCode' => 'HN-CG-01', 'WardName' => 'Dich Vong Hau'],
                        ['WardCode' => 'HN-CG-02', 'WardName' => 'Nghia Tan'],
                        ['WardCode' => 'HN-CG-03', 'WardName' => 'Yen Hoa'],
                    ],
                ],
                [
                    'DistrictID' => 1460,
                    'DistrictName' => 'Hoang Mai',
                    'Code' => 'HN-HM',
                    'wards' => [
                        ['WardCode' => 'HN-HM-01', 'WardName' => 'Dai Kim'],
                        ['WardCode' => 'HN-HM-02', 'WardName' => 'Dinh Cong'],
                        ['WardCode' => 'HN-HM-03', 'WardName' => 'Hoang Liet'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 202,
            'ProvinceName' => 'Ho Chi Minh',
            'Code' => 'HCM',
            'NameExtension' => ['Ho Chi Minh', 'TP HCM', 'Sai Gon'],
            'districts' => [
                [
                    'DistrictID' => 1442,
                    'DistrictName' => 'Quan 1',
                    'Code' => 'HCM-Q1',
                    'wards' => [
                        ['WardCode' => 'HCM-Q1-01', 'WardName' => 'Ben Nghe'],
                        ['WardCode' => 'HCM-Q1-02', 'WardName' => 'Da Kao'],
                        ['WardCode' => 'HCM-Q1-03', 'WardName' => 'Tan Dinh'],
                    ],
                ],
                [
                    'DistrictID' => 1447,
                    'DistrictName' => 'Quan 7',
                    'Code' => 'HCM-Q7',
                    'wards' => [
                        ['WardCode' => 'HCM-Q7-01', 'WardName' => 'Tan Phu'],
                        ['WardCode' => 'HCM-Q7-02', 'WardName' => 'Tan Quy'],
                        ['WardCode' => 'HCM-Q7-03', 'WardName' => 'Phu My'],
                    ],
                ],
                [
                    'DistrictID' => 1452,
                    'DistrictName' => 'Thanh Pho Thu Duc',
                    'Code' => 'HCM-TD',
                    'wards' => [
                        ['WardCode' => 'HCM-TD-01', 'WardName' => 'Hiep Binh Chanh'],
                        ['WardCode' => 'HCM-TD-02', 'WardName' => 'Linh Tay'],
                        ['WardCode' => 'HCM-TD-03', 'WardName' => 'An Khanh'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 203,
            'ProvinceName' => 'Da Nang',
            'Code' => 'DN',
            'NameExtension' => ['Da Nang', 'Danang'],
            'districts' => [
                [
                    'DistrictID' => 3001,
                    'DistrictName' => 'Hai Chau',
                    'Code' => 'DN-HC',
                    'wards' => [
                        ['WardCode' => 'DN-HC-01', 'WardName' => 'Hai Chau 1'],
                        ['WardCode' => 'DN-HC-02', 'WardName' => 'Hai Chau 2'],
                        ['WardCode' => 'DN-HC-03', 'WardName' => 'Hoa Cuong Bac'],
                    ],
                ],
                [
                    'DistrictID' => 3002,
                    'DistrictName' => 'Thanh Khe',
                    'Code' => 'DN-TK',
                    'wards' => [
                        ['WardCode' => 'DN-TK-01', 'WardName' => 'Thanh Khe Dong'],
                        ['WardCode' => 'DN-TK-02', 'WardName' => 'Thanh Khe Tay'],
                        ['WardCode' => 'DN-TK-03', 'WardName' => 'Xuan Ha'],
                    ],
                ],
                [
                    'DistrictID' => 3003,
                    'DistrictName' => 'Son Tra',
                    'Code' => 'DN-ST',
                    'wards' => [
                        ['WardCode' => 'DN-ST-01', 'WardName' => 'An Hai Bac'],
                        ['WardCode' => 'DN-ST-02', 'WardName' => 'An Hai Tay'],
                        ['WardCode' => 'DN-ST-03', 'WardName' => 'Man Thai'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 204,
            'ProvinceName' => 'Hai Phong',
            'Code' => 'HP',
            'NameExtension' => ['Hai Phong'],
            'districts' => [
                [
                    'DistrictID' => 3101,
                    'DistrictName' => 'Hong Bang',
                    'Code' => 'HP-HB',
                    'wards' => [
                        ['WardCode' => 'HP-HB-01', 'WardName' => 'Hoang Van Thu'],
                        ['WardCode' => 'HP-HB-02', 'WardName' => 'Minh Khai'],
                        ['WardCode' => 'HP-HB-03', 'WardName' => 'So Dau'],
                    ],
                ],
                [
                    'DistrictID' => 3102,
                    'DistrictName' => 'Ngo Quyen',
                    'Code' => 'HP-NQ',
                    'wards' => [
                        ['WardCode' => 'HP-NQ-01', 'WardName' => 'Cau Dat'],
                        ['WardCode' => 'HP-NQ-02', 'WardName' => 'Dang Giang'],
                        ['WardCode' => 'HP-NQ-03', 'WardName' => 'Dong Khe'],
                    ],
                ],
                [
                    'DistrictID' => 3103,
                    'DistrictName' => 'Le Chan',
                    'Code' => 'HP-LC',
                    'wards' => [
                        ['WardCode' => 'HP-LC-01', 'WardName' => 'An Bien'],
                        ['WardCode' => 'HP-LC-02', 'WardName' => 'Cat Dai'],
                        ['WardCode' => 'HP-LC-03', 'WardName' => 'Du Hang Kenh'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 205,
            'ProvinceName' => 'Can Tho',
            'Code' => 'CT',
            'NameExtension' => ['Can Tho'],
            'districts' => [
                [
                    'DistrictID' => 3201,
                    'DistrictName' => 'Ninh Kieu',
                    'Code' => 'CT-NK',
                    'wards' => [
                        ['WardCode' => 'CT-NK-01', 'WardName' => 'Tan An'],
                        ['WardCode' => 'CT-NK-02', 'WardName' => 'An Cu'],
                        ['WardCode' => 'CT-NK-03', 'WardName' => 'Xuan Khanh'],
                    ],
                ],
                [
                    'DistrictID' => 3202,
                    'DistrictName' => 'Binh Thuy',
                    'Code' => 'CT-BT',
                    'wards' => [
                        ['WardCode' => 'CT-BT-01', 'WardName' => 'Binh Thuy'],
                        ['WardCode' => 'CT-BT-02', 'WardName' => 'An Thoi'],
                        ['WardCode' => 'CT-BT-03', 'WardName' => 'Long Hoa'],
                    ],
                ],
                [
                    'DistrictID' => 3203,
                    'DistrictName' => 'Cai Rang',
                    'Code' => 'CT-CR',
                    'wards' => [
                        ['WardCode' => 'CT-CR-01', 'WardName' => 'Le Binh'],
                        ['WardCode' => 'CT-CR-02', 'WardName' => 'Hung Phu'],
                        ['WardCode' => 'CT-CR-03', 'WardName' => 'Ba Lang'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 206,
            'ProvinceName' => 'Thai Nguyen',
            'Code' => 'TN',
            'NameExtension' => ['Thai Nguyen'],
            'districts' => [
                [
                    'DistrictID' => 3301,
                    'DistrictName' => 'Thanh Pho Thai Nguyen',
                    'Code' => 'TN-TP',
                    'wards' => [
                        ['WardCode' => 'TN-TP-01', 'WardName' => 'Phan Dinh Phung'],
                        ['WardCode' => 'TN-TP-02', 'WardName' => 'Hoang Van Thu'],
                        ['WardCode' => 'TN-TP-03', 'WardName' => 'Quang Trung'],
                    ],
                ],
                [
                    'DistrictID' => 3302,
                    'DistrictName' => 'Song Cong',
                    'Code' => 'TN-SC',
                    'wards' => [
                        ['WardCode' => 'TN-SC-01', 'WardName' => 'Bach Quang'],
                        ['WardCode' => 'TN-SC-02', 'WardName' => 'Mo Che'],
                        ['WardCode' => 'TN-SC-03', 'WardName' => 'Thang Loi'],
                    ],
                ],
                [
                    'DistrictID' => 3303,
                    'DistrictName' => 'Pho Yen',
                    'Code' => 'TN-PY',
                    'wards' => [
                        ['WardCode' => 'TN-PY-01', 'WardName' => 'Ba Hang'],
                        ['WardCode' => 'TN-PY-02', 'WardName' => 'Dong Tien'],
                        ['WardCode' => 'TN-PY-03', 'WardName' => 'Tien Phong'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 207,
            'ProvinceName' => 'Lam Dong',
            'Code' => 'LD',
            'NameExtension' => ['Lam Dong', 'Da Lat'],
            'districts' => [
                [
                    'DistrictID' => 3401,
                    'DistrictName' => 'Thanh Pho Da Lat',
                    'Code' => 'LD-DL',
                    'wards' => [
                        ['WardCode' => 'LD-DL-01', 'WardName' => 'Phuong 1'],
                        ['WardCode' => 'LD-DL-02', 'WardName' => 'Phuong 2'],
                        ['WardCode' => 'LD-DL-03', 'WardName' => 'Phuong 10'],
                    ],
                ],
                [
                    'DistrictID' => 3402,
                    'DistrictName' => 'Bao Loc',
                    'Code' => 'LD-BL',
                    'wards' => [
                        ['WardCode' => 'LD-BL-01', 'WardName' => 'Loc Son'],
                        ['WardCode' => 'LD-BL-02', 'WardName' => 'Loc Tien'],
                        ['WardCode' => 'LD-BL-03', 'WardName' => 'Blao'],
                    ],
                ],
                [
                    'DistrictID' => 3403,
                    'DistrictName' => 'Duc Trong',
                    'Code' => 'LD-DT',
                    'wards' => [
                        ['WardCode' => 'LD-DT-01', 'WardName' => 'Lien Nghia'],
                        ['WardCode' => 'LD-DT-02', 'WardName' => 'Hiep An'],
                        ['WardCode' => 'LD-DT-03', 'WardName' => 'Phu Hoi'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 208,
            'ProvinceName' => 'Son La',
            'Code' => 'SL',
            'NameExtension' => ['Son La'],
            'districts' => [
                [
                    'DistrictID' => 3501,
                    'DistrictName' => 'Thanh Pho Son La',
                    'Code' => 'SL-TP',
                    'wards' => [
                        ['WardCode' => 'SL-TP-01', 'WardName' => 'Chieng Le'],
                        ['WardCode' => 'SL-TP-02', 'WardName' => 'To Hieu'],
                        ['WardCode' => 'SL-TP-03', 'WardName' => 'Quyet Tam'],
                    ],
                ],
                [
                    'DistrictID' => 3502,
                    'DistrictName' => 'Moc Chau',
                    'Code' => 'SL-MC',
                    'wards' => [
                        ['WardCode' => 'SL-MC-01', 'WardName' => 'Moc Ly'],
                        ['WardCode' => 'SL-MC-02', 'WardName' => 'Dong Sang'],
                        ['WardCode' => 'SL-MC-03', 'WardName' => 'Thao Nguyen'],
                    ],
                ],
                [
                    'DistrictID' => 3503,
                    'DistrictName' => 'Mai Son',
                    'Code' => 'SL-MS',
                    'wards' => [
                        ['WardCode' => 'SL-MS-01', 'WardName' => 'Hat Lot'],
                        ['WardCode' => 'SL-MS-02', 'WardName' => 'Chieng Mung'],
                        ['WardCode' => 'SL-MS-03', 'WardName' => 'Co Noi'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 209,
            'ProvinceName' => 'Soc Trang',
            'Code' => 'ST',
            'NameExtension' => ['Soc Trang'],
            'districts' => [
                [
                    'DistrictID' => 3601,
                    'DistrictName' => 'Thanh Pho Soc Trang',
                    'Code' => 'ST-TP',
                    'wards' => [
                        ['WardCode' => 'ST-TP-01', 'WardName' => 'Phuong 1'],
                        ['WardCode' => 'ST-TP-02', 'WardName' => 'Phuong 2'],
                        ['WardCode' => 'ST-TP-03', 'WardName' => 'Phuong 3'],
                    ],
                ],
                [
                    'DistrictID' => 3602,
                    'DistrictName' => 'My Xuyen',
                    'Code' => 'ST-MX',
                    'wards' => [
                        ['WardCode' => 'ST-MX-01', 'WardName' => 'Thi Tran My Xuyen'],
                        ['WardCode' => 'ST-MX-02', 'WardName' => 'Hoa Tu 1'],
                        ['WardCode' => 'ST-MX-03', 'WardName' => 'Dai Tam'],
                    ],
                ],
                [
                    'DistrictID' => 3603,
                    'DistrictName' => 'Nga Nam',
                    'Code' => 'ST-NN',
                    'wards' => [
                        ['WardCode' => 'ST-NN-01', 'WardName' => 'Phuong 1'],
                        ['WardCode' => 'ST-NN-02', 'WardName' => 'Phuong 2'],
                        ['WardCode' => 'ST-NN-03', 'WardName' => 'Long Binh'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 210,
            'ProvinceName' => 'Dak Lak',
            'Code' => 'DLK',
            'NameExtension' => ['Dak Lak', 'Buon Ma Thuot'],
            'districts' => [
                [
                    'DistrictID' => 3701,
                    'DistrictName' => 'Buon Ma Thuot',
                    'Code' => 'DLK-BMT',
                    'wards' => [
                        ['WardCode' => 'DLK-BMT-01', 'WardName' => 'Tan Loi'],
                        ['WardCode' => 'DLK-BMT-02', 'WardName' => 'Tan An'],
                        ['WardCode' => 'DLK-BMT-03', 'WardName' => 'Thanh Nhat'],
                    ],
                ],
                [
                    'DistrictID' => 3702,
                    'DistrictName' => 'Krong Pak',
                    'Code' => 'DLK-KP',
                    'wards' => [
                        ['WardCode' => 'DLK-KP-01', 'WardName' => 'Phuoc An'],
                        ['WardCode' => 'DLK-KP-02', 'WardName' => 'Ea Kly'],
                        ['WardCode' => 'DLK-KP-03', 'WardName' => 'Hoa An'],
                    ],
                ],
                [
                    'DistrictID' => 3703,
                    'DistrictName' => "Ea H'leo",
                    'Code' => 'DLK-EH',
                    'wards' => [
                        ['WardCode' => 'DLK-EH-01', 'WardName' => 'Ea Drang'],
                        ['WardCode' => 'DLK-EH-02', 'WardName' => 'Ea Hiao'],
                        ['WardCode' => 'DLK-EH-03', 'WardName' => 'Dlie Yang'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 211,
            'ProvinceName' => 'Quang Ninh',
            'Code' => 'QN',
            'NameExtension' => ['Quang Ninh', 'Ha Long'],
            'districts' => [
                [
                    'DistrictID' => 3801,
                    'DistrictName' => 'Ha Long',
                    'Code' => 'QN-HL',
                    'wards' => [
                        ['WardCode' => 'QN-HL-01', 'WardName' => 'Bai Chay'],
                        ['WardCode' => 'QN-HL-02', 'WardName' => 'Hong Gai'],
                        ['WardCode' => 'QN-HL-03', 'WardName' => 'Cao Xanh'],
                    ],
                ],
                [
                    'DistrictID' => 3802,
                    'DistrictName' => 'Cam Pha',
                    'Code' => 'QN-CP',
                    'wards' => [
                        ['WardCode' => 'QN-CP-01', 'WardName' => 'Cam Tay'],
                        ['WardCode' => 'QN-CP-02', 'WardName' => 'Cam Trung'],
                        ['WardCode' => 'QN-CP-03', 'WardName' => 'Cam Thanh'],
                    ],
                ],
                [
                    'DistrictID' => 3803,
                    'DistrictName' => 'Uong Bi',
                    'Code' => 'QN-UB',
                    'wards' => [
                        ['WardCode' => 'QN-UB-01', 'WardName' => 'Quang Trung'],
                        ['WardCode' => 'QN-UB-02', 'WardName' => 'Thanh Son'],
                        ['WardCode' => 'QN-UB-03', 'WardName' => 'Bac Son'],
                    ],
                ],
            ],
        ],
        [
            'ProvinceID' => 212,
            'ProvinceName' => 'Khanh Hoa',
            'Code' => 'KH',
            'NameExtension' => ['Khanh Hoa', 'Nha Trang'],
            'districts' => [
                [
                    'DistrictID' => 3901,
                    'DistrictName' => 'Nha Trang',
                    'Code' => 'KH-NT',
                    'wards' => [
                        ['WardCode' => 'KH-NT-01', 'WardName' => 'Loc Tho'],
                        ['WardCode' => 'KH-NT-02', 'WardName' => 'Vinh Hoa'],
                        ['WardCode' => 'KH-NT-03', 'WardName' => 'Phuoc Hai'],
                    ],
                ],
                [
                    'DistrictID' => 3902,
                    'DistrictName' => 'Cam Ranh',
                    'Code' => 'KH-CR',
                    'wards' => [
                        ['WardCode' => 'KH-CR-01', 'WardName' => 'Cam Loi'],
                        ['WardCode' => 'KH-CR-02', 'WardName' => 'Cam Linh'],
                        ['WardCode' => 'KH-CR-03', 'WardName' => 'Ba Ngoi'],
                    ],
                ],
                [
                    'DistrictID' => 3903,
                    'DistrictName' => 'Dien Khanh',
                    'Code' => 'KH-DK',
                    'wards' => [
                        ['WardCode' => 'KH-DK-01', 'WardName' => 'Dien An'],
                        ['WardCode' => 'KH-DK-02', 'WardName' => 'Dien Toan'],
                        ['WardCode' => 'KH-DK-03', 'WardName' => 'Dien Phu'],
                    ],
                ],
            ],
        ],
    ];
}
