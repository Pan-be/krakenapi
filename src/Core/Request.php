<?php

namespace Core;

class Request
{
    public static function getIntervalFromQuery(array $allowed = []): string
    {
        $interval = isset($_GET['interval']) ? (string) $_GET['interval'] : '1h';

        if (!in_array($interval, $allowed)) {
            http_response_code(400);
            echo json_encode(["error" => "Unsupported or missing interval"]);
            exit;
        }

        return $interval;
    }
}
