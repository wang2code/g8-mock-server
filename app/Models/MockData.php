<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectID;

class MockData extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'mock';

    public static function getMockDataByID($mock_user, $oid, $parameters)
    {
        $mock_dataset = MockData::getUserSettingsSortByParamsCount($mock_user, $oid);
        foreach ($mock_dataset as $data) {
            $params_count = count($parameters);
            foreach ($parameters as $key => $req_value) {
                if (isset($data->params[$key])) {
                    $params_count--;
                }
            }
            if ($params_count == 0) {
                return $data;
            }
        }
        return null;
    }

    public static function getMockData($mock_user, $target_path, $parameters)
    {
        $mock_dataset = MockData::getUserSettingsSortByParamsCount($mock_user, null, $target_path);
        foreach ($mock_dataset as $data) {
            $params_count = count($parameters);
            foreach ($parameters as $key => $req_value) {
                if (isset($data->params[$key])) {
                    $params_count--;
                }
            }
            if ($params_count == 0) {
                return $data;
            }
        }
        return null;
    }

    /**
     * 取得使用者的全部設定
     * 
     * @param int $mock_user (required) 使用者名稱
     * @param string $oid (nullable) 指定 mongodb doc id
     * @param string $target_path (nullable) 路徑
     * 
     * @return [MockData]
     */
    public static function getUserSettingsSortByParamsCount($mock_user, $oid=null, $target_path=null)
    {
        $match_cond = [
            'mock_user' => $mock_user,
        ];
        if ($oid) {
            $match_cond['_id'] = new ObjectID($oid);
        }
        if ($target_path) {
            $match_cond['target_path'] = $target_path;
        }

        $raw_aggregate = [
            [
                '$match' => $match_cond,
            ],
            [
                '$addFields' => [
                    'params_array' => [
                        '$objectToArray' => '$$ROOT.params'
                    ]
                ]
            ],
            [
                '$addFields' => [
                    'params_count' => [
                        '$size' => [
                            '$params_array'
                        ]
                    ]
                ]
            ],
            [
                '$sort' => [
                    'params_count' => -1
                ]
            ]
        ];
        return self::raw(function ($collection) use ($raw_aggregate) {
            return $collection->aggregate($raw_aggregate);
        });
    }

}
