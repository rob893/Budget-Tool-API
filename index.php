<?php
declare(strict_types = 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json');
header('Accept: application/json');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET)) {
    http_response_code(200);
    echo file_get_contents('data.json');
}
else if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST)) {
    
    $data = file_get_contents('data.json');
    $data = json_decode($data, true);
    
    $jsonStr = file_get_contents('php://input');
    $jsonObj = json_decode($jsonStr, true);
   
    if (array_key_exists('Annual', $jsonObj)) {
        if (!array_key_exists('name', $jsonObj['Annual']) || !array_key_exists('expense', $jsonObj['Annual'])) {
            echo 'Invalid post body.';
            throw new \Exception('Invalid post body.', 500);
        }

        $data['Annual'][] = $jsonObj['Annual'];
        $data = json_encode($data);

        if (!file_put_contents('data.json', $data)) {
            print_r(error_get_last());
            throw new \Exception('Could not save file!', 500);
        }

        http_response_code(201);
    }
    else if (array_key_exists('Monthly', $jsonObj)) {
        if (!array_key_exists('name', $jsonObj['Monthly']) || !array_key_exists('expense', $jsonObj['Monthly'])) {
            echo 'Invalid post body.';
            throw new \Exception('Invalid post body.', 500);
        }

        $data['Monthly'][] = $jsonObj['Monthly'];
        $data = json_encode($data);

        if (!file_put_contents('data.json', $data)) {
            print_r(error_get_last());
            throw new \Exception('Could not save file!', 500);
        }

        http_response_code(201);
    }
    else if (array_key_exists('Delete', $jsonObj) && array_key_exists('From', $jsonObj)) {
        if (!array_key_exists($jsonObj['From'], $data)) {
            echo 'Invalid post body for delete.';
            print_r($jsonObj);
            print_r($data);
            throw new \Exception('Invalid post body.', 500);
        }

        $found = false;
        $newItems = [];
        foreach ($data[$jsonObj['From']] as $expense) {
            if ($expense['name'] === $jsonObj['Delete']) {
                $found = true;
                continue;
            }
            $newItems[] = $expense;
        }
        
        
        if (!$found) {
            echo 'Item to be deleted not found!';
            throw new \Exception('Invalid delete item.', 404);
        }

        $data[$jsonObj['From']] = $newItems;
        $data = json_encode($data);

        if (!file_put_contents('data.json', $data)) {
            print_r(error_get_last());
            throw new \Exception('Could not save file!', 500);
        }

        http_response_code(200);
    }
    else if (array_key_exists('reset', $jsonObj)) {
        $backUpData = file_get_contents('backupData.json');

        if (!file_put_contents('data.json', $backUpData)) {
            print_r(error_get_last());
            throw new \Exception('Could not save file!', 500);
        }

        http_response_code(200);
    }
    else {
        echo 'Invalid post body.';
        throw new \Exception('Invalid post body.', 500);
    }
}
else {
    http_response_code(200);
}

unset($_REQUEST);
unset($_GET);
unset($_POST);