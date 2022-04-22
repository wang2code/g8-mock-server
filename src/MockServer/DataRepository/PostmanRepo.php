<?php

namespace src\MockServer\DataRepository;

//  postman json 範例
//{
//    "info": {
//        ...
//    },
//    "item": [
//        {
//            "name": "模組化頁面", <- section
//            "item": [
//                {
//                    "name": "/dynamic/pages", <- path
//                    "response": [
//                        {
//                            "name": "3組", <- sample data name
//                            "body": "{\n    \"name\": \"3組\"\n}"  <- sample data
//                        },
//                        ...
//                    ]
//                },
//                ...
//            ]
//        },
//        ...
//    ]
//}
class PostmanRepo extends BaseDataRepository {

    private string $filename = 'kkday.postman_collection.json';

    private $json;

    public function __construct()
    {
        parent::__construct();

        $this->json = json_decode($this->raw_data, true);
    }

    protected function getMockDataFilename(): string
    {
        return $this->filename;
    }

    //  ------ DataRepositoryImp Start ------

    public function name(): string
    {
        return "Postman";
    }

    public function getSections(): array
    {
        return array_map(function($item) {
            return $item['name'];
        }, $this->json["item"]);
    }

    public function getPaths(int $section): array
    {
        if (count($this->json["item"]) <= $section) {
            return [];
        }
        $paths = [];
        foreach ($this->json['item'][$section]['item'] as $item) {
            $paths[] = $item["name"];
        }
        return $paths;
    }

    public function getSampleDataNames(int $section, int $path_index): array
    {
        return array_map(function($response) {
            return $response['name'];
        }, $this->json['item'][$section]['item'][$path_index]['response']);
    }

    public function getSampleData(int $section, int $path_index, int $data_index): string
    {
        return $this->json['item'][$section]['item'][$path_index]['response'][$data_index]['body'];
    }

    //  ------ DataRepositoryImp End ------

}
