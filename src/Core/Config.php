<?php

namespace Core;

class Config
{
    public static function load(string $name): array
    {
        $path = __DIR__ . '/../../config/' . $name . '.json';

        if (!file_exists($path)) {
            throw new \Exception("Config file '$name' not found");
        }

        $data = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error decoding '$name.json': " . json_last_error_msg());
        }

        return $data;
    }
}
