<?php

namespace App\Http\Controllers;

use App\Models\MockData;
use Illuminate\Http\Request;


class MockController extends Controller
{

    public function __invoke(Request $request)
    {
        try {
            $mock_user = $request->get('mock_user');

            if (empty($mock_user)) {
                throw new \Exception('抓不到 mock user', 1);
            }

            $target_path = $this->getTargetPath($request);
            $mock_response = $this->getMockResponse($request, $mock_user, $target_path);

            if ($mock_response) {
                return response()->json(json_decode($mock_response, true));
            }

            //  TODO: get from mock user settings.
            $fallback_domain = 'https://localhost';
            $result = $this->redirect($request, $fallback_domain . $target_path);

            return response()->json(json_decode($result, true));

        } catch (\Exception $e) {
            return response()->json([
                'errmsg' => $e->getMessage(),
            ]);
        }

    }

    /**
     * 轉導該次請求
     *
     * @param Request $request
     * @param string $url
     *
     * @throws \Exception
     *
     * @return string
     */
    private function redirect(Request $request, string $url): string
    {
        $ch = curl_init();

        $queries = $request->query();
        if ($queries) {
            $url = $url."?".http_build_query($queries);
        }

        if (strtolower($request->method()) == "post") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request->post(), null, '&'));
        }

        $cookies = $_COOKIE;
        if ($cookies && count($cookies) > 0) {
            $cookie_strings = [];
            foreach ($cookies as $name => $value) {
                $cookie_strings[] = "{$name}={$value}";
            }
            curl_setopt($ch, CURLOPT_COOKIE, implode(";", $cookie_strings));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);

        if ($output === false) {
            throw new \Exception(curl_error($ch), 1);
        }

        return $output;
    }

    /**
     * 取得要回傳的假資料
     *
     * @param Request $request
     * @param string $mock_user
     * @param string $target_path
     *
     * @return string|null response json string
     */
    private function getMockResponse(Request $request, string $mock_user, string $target_path): ?string
    {
        $mock_data = MockData::getAllAndSortByParamsCount($mock_user, $target_path);

        //  從參數最多的開始匹配，全部都命中的話 params_count 會等於 0
        //  如果 params_count == 0 表示命中，則回傳
        //  如果都沒參數要比的話，也表示命中
        foreach ($mock_data as $data) {
            $params_count = count($data->params);
            foreach ($data->params as $key => $value) {
                $req_value = $request->input($key);
                if ($req_value !== null && $req_value == $value) {
                    $params_count--;
                }
            }
            if ($params_count == 0) {
                return $data->response;
            }
        }
        return null;
    }

    /**
     * 取得要 mock 的 path，就是接在 /mock 後面的路徑
     *
     * @param Request $request
     *
     * @return string
     */
    private function getTargetPath(Request $request): string
    {
        $target_path = $request->getPathInfo();
        return preg_replace('/^\/mock/i', '', $target_path);
    }

}
