<?php

namespace App\Http\Controllers;

use App\Models\MockData;
use Illuminate\Http\Request;
use src\MockServer\MockDataFacade;

class DevController extends Controller
{

    public function settings()
    {
        return view('dev_settings', ['tab' => 'settings']);
    }

    public function mock(Request $request)
    {
        $select = $request->query('select') ?? '';
        $index = explode(",", $select);
        $repo_index = 0;//$index[0] ?? -1;
        $section_index = $index[1] ?? -1;
        $path_index = $index[2] ?? -1;
        $sample_index = $index[3] ?? -1;

        $mock_user = $request->get('mock_user');

        $mock_user_settings = [
            //  _id => path
        ];
        if ($mock_user) {
            $mock_datas = MockData::getUserSettingsSortByParamsCount($mock_user);
            foreach ($mock_datas as $data) {
                $pramas_string = '';
                if ($data->params) {
                    $params_strings = [];
                    foreach ($data->params as $key => $value) {
                        $params_strings[] = $key . '=' . $value;
                    }
                    sort($params_strings);
                    $pramas_string = implode("&", $params_strings);
                }
                $mock_user_settings[$data->_id] = $data->target_path . ($pramas_string ? '?' . $pramas_string : '');
            }
            krsort($mock_user_settings);
        }

        $facade = new MockDataFacade();
        $repo_names = $facade->getRepoNames();

        $sections = [];
        $paths = [];
        $sample_names = [];
        $sample_data = null;

        if (is_numeric($repo_index) && intval($repo_index) >= 0) {
            $facade->select($repo_index);
            $sections = $facade->getSections();
        }
        if (is_numeric($section_index) && intval($section_index) >= 0) {
            $paths = $facade->getPaths($section_index);

            if (is_numeric($path_index) && intval($path_index) >= 0) {
                $sample_names = $facade->getSampleDataNames($section_index, $path_index);

                if (is_numeric($sample_index) && intval($sample_index) >= 0) {
                    $sample_data = $facade->getSampleData($section_index, $path_index, $sample_index);
                }
            }
        }

        $view_data = [
            'tab'           => 'mock',
            'mock_user'     => $mock_user,
            'repo_names'    => $repo_names,
            'sections'      => $sections,
            'paths'         => $paths,
            'sample_names'  => $sample_names,
            'sample_data'   => $sample_data,
            'repo_index'    => $repo_index,
            'section_index' => $section_index,
            'path_index'    => $path_index,
            'sample_index'  => $sample_index,
            'mock_user_settings' => $mock_user_settings,
        ];

        return view('mock', $view_data);
    }

}
