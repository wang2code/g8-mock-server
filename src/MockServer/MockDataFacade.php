<?php

namespace src\MockServer;

use src\MockServer\DataRepository\BaseDataRepository;
use src\MockServer\DataRepository\PostmanRepo;

class MockDataFacade {

    private array $repos;
    private BaseDataRepository $current_repo;

    function __construct()
    {
        $this->repos[] = new PostmanRepo();
    }

    /**
     * 取得目前支援的資料來源
     *
     * @return string[]
     */
    public function getRepoNames(): array
    {
        return array_map(function($repo) {
            return $repo->name();
        }, $this->repos);
    }

    public function select($repo_index)
    {
        if (is_numeric($repo_index) && $repo_index >= 0) {
            $this->current_repo = $this->repos[$repo_index];
        }
    }

    public function getSections(): array
    {
        return $this->current_repo->getSections();
    }

    public function getPaths($section): array
    {
        return $this->current_repo->getPaths($section);
    }

    public function getSampleDataNames(int $section, int $path_index): array
    {
        return $this->current_repo->getSampleDataNames($section, $path_index);
    }

    public function getSampleData(int $section, int $path_index, int $data_index): string
    {
        return $this->current_repo->getSampleData($section, $path_index, $data_index);
    }

}
