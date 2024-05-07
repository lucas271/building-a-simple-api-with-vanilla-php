<?php


class ProductController{

    public function __construct(private ProductGateway $gateway)
    {
        
    }

    public function processRequest(string $method, ?string $id=null): void{
        var_dump($method, $id);

        if($id){
            $this->processResourceRequest($method, $id);
        }else{
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void{

        $product = $this->gateway->get($id);

        if(!$product){
            http_response_code(404);
            echo json_encode(["message" => "not found"]);
        }

        switch ($method) {
            case 'GET':
                echo json_encode($product);
                break;
            case 'PATCH':
                $data = (array) json_decode(file_get_contents('php://input'), true);
                $errors = $this->getValidationErrors($data, false);

                if(!empty($error)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($product, $data);

                echo json_encode([
                    "message" => "product created",
                    "rows" => $rows
                ]);
            case 'DELETE':
                $rows = $this->gateway->delete($id);

                echo json_encode(["message" => "product $id deleted", "row" => $rows]);
                break;
            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
                break;
        }

    }

    private function processCollectionRequest(string $method): void{
        switch ($method) {
            case 'GET':
                echo json_encode($this->gateway->getAll());
                break;
            

            case 'POST': 
                $data = (array) json_decode(file_get_contents('php://input'), true);
                $errors = $this->getValidationErrors($data);

                if(!empty($error)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "product created",
                    "id" => $id
                ]);
                break;
            default:
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }

    private function getValidationErrors(array $data, bool $is_new=true): array{
        $errors = [];
        if(empty($data["name"]) && $is_new){
            $error[] = "name is required";
        }
        if(array_key_exists("size", $data)){
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false){
                $errors[] = "size must be a integer";
            }
        }

        return $errors;
    }
}