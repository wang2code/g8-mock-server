<?php

namespace App\Http\Controllers;

use App\Models\MockData;
use Illuminate\Http\Request;
use Mockery\Mock;
use src\MockServer\MockDataFacade;

class ApiController extends Controller
{

    public function getMockPathData(Request $request)
    {
        try {
            $section = $request->query('section');
            $facade = new MockDataFacade();
            $facade->select(0);
            $result_data = $facade->getPaths($section);

            return response()->json([
                'data' => $result_data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'data'  => [],
            ]);
        }
    }

    public function getMockSampleNamesData(Request $request)
    {
        try {
            $section = $request->query('section');
            $path = $request->query('path');
            $facade = new MockDataFacade();
            $facade->select(0);
            $result_data = $facade->getSampleDataNames($section, $path);

            return response()->json([
                'data' => $result_data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'data'  => [],
            ]);
        }
    }

    public function getMockSampleData(Request $request)
    {
        try {
            $section = $request->query('section');
            $path = $request->query('path');
            $sample = $request->query('sample');
            $facade = new MockDataFacade();
            $facade->select(0);
            $result_data = $facade->getSampleData($section, $path, $sample);

            return response()->json([
                'data' => json_encode(json_decode($result_data, true), JSON_UNESCAPED_UNICODE),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'data'  => [],
            ]);
        }
    }

    public function UpdateUserFakeData(Request $request)
    {
        try {
            $mock_user = $request->get('mock_user');
            if (empty($mock_user)) {
                throw new \Exception("沒有 mock user");
            }

            $body = $request->post("body");
            $target_path = $request->post('path');
            $params = $request->post('params');

            $parameters = [];
            foreach ($params as $param) {
                $items = explode("=", $param);
                if (count($items) > 1) {
                    $parameters[$items[0]] = $items[1];
                }
            }

            $mock_data = MockData::getMockData($mock_user, $target_path, $parameters) ?? new MockData();
            $mock_data->mock_user = $mock_user;
            $mock_data->target_path = $target_path;
            $mock_data->response = $body;
            $mock_data->params = (object) $parameters;
            $mock_data->save();

            return response()->json([
                'data' => '',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

}


