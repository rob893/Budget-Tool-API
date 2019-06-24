<?php
declare(strict_types = 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Accept: application/json');

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

        $itemToDeleteKey = -1;
        foreach ($data[$jsonObj['From']] as $key => $expense) {
            if ($expense['name'] === $jsonObj['Delete']) {
                $itemToDeleteKey = $key;
                break;
            }
        }
        
        if ($itemToDeleteKey !== -1) {
            unset($data[$jsonObj['From']][$itemToDeleteKey]);
        }
        else {
            echo 'Item to be deleted not found!';
            throw new \Exception('Invalid delete item.', 404);
        }
        

        $data = json_encode($data);

        if (!file_put_contents('data.json', $data)) {
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
    http_response_code(500);
}

unset($_REQUEST);
unset($_GET);
unset($_POST);