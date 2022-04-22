<?php

namespace src\MockServer\DataRepository;

use Illuminate\Support\Facades\Storage;

abstract class BaseDataRepository implements DataRepositoryImpl {

    protected $raw_data;

    public function __construct()
    {
        $filename = $this->getMockDataFilename();
        $this->raw_data = Storage::disk('local')->get("/mock/{$filename}");
    }

    public function upload(string $data)
    {
        $filename = $this->getMockDataFilename();
        Storage::disk('local')->put("/mock/{$filename}", $data);
    }

    abstract protected function getMockDataFilename(): string;

}
