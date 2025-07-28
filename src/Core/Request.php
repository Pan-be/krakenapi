<?php

namespace Core;

class Request
{
    public static function getIntervalFromQuery(array $allowed = []): int
    {
        $interval = isset($_GET['interval']) ? (int) $_GET['interval'] : 0;

        if (!in_array($interval, $allowed)) {
            http_response_code(400);
            echo json_encode(["error" => "Unsupported or missing interval"]);
            exit;
        }

        return $interval;
    }
}
