<?php

namespace src\MockServer\DataRepository;

interface DataRepositoryImpl {

    /**
     * 這個 repo 的名字
     *
     * @return string
     */
    public function name(): string;

    /**
     * 取得 mock data 的分類, ["首頁", "商品頁", ...]
     *
     * @return string[]
     */
    public function getSections(): array;

    /**
     * 取得 mock data 的路徑 ["/home", "/product", ...]
     *
     * @param int $section 哪個分類
     *
     * @return string[]
     */
    public function getPaths(int $section): array;

    /**
     * 取得該路徑底下所有範例名稱
     *
     * @param int $section 哪個分類
     * @param int $path_index 哪個路徑
     *
     * @return string[]
     */
    public function getSampleDataNames(int $section, int $path_index): array;

    /**
     * 取得範例資料
     *
     * @param int $section 哪個分類
     * @param int $path_index 哪個路徑
     * @param int $data_index 哪個範例
     * @return string
     */
    public function getSampleData(int $section, int $path_index, int $data_index): string;


}
