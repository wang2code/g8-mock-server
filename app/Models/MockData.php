<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MockData extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'mock';

    public static function getMockData($mock_user, $target_path, $parameters)
    {
        $mock_dataset = MockData::getAllAndSortByParamsCount($mock_user, $target_path);
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

    public static function getAllAndSortByParamsCount($mock_user, $target_path)
    {
        $raw_aggregate = [
            [
                '$match' => [
                    'mock_user'   => $mock_user,
                    'target_path' => $target_path
                ]
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
