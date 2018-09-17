<?php

namespace AppBundle\Model;

class FormValidate {
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function validateCreatePayment()
    {
        $requiredFields = ["type", "date", "amount"];

        $errors = []; 
        foreach ($requiredFields as $required) {
            if ($this->request->request->get($required)) {
                if ($required == 'date') {
                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $this->request->request->get($required))) {
                        $errors[] = "valid date is yyyy-mm-dd";
                    }
                }
            } 
            else {
                $errors[] = "$required field is required.";
            }
        }

        if ($errors) {
            return ["errors" => $errors];
        }
        else {
            return ["success" => true]; 
        }
    }

    public function validateSignup()
    {
        $requiredFields = ["email", "password", "aggree"];

        $errors = []; 
        foreach ($requiredFields as $required) {
            if (!$this->request->request->get($required)) {
                if ($required == 'aggree') {
                    $errors[] = "You need to agree Terms and Condition.";
                }
                else {
                    $errors[] = "$required field is required.";
                }
            }
        }

        if ($errors) {
            return ["errors" => $errors];
        }
        else {
            return ["success" => true]; 
        }
    }
}
