<?php

namespace app\core;

abstract class Model
{
    public static $RULE_REQUIRED = 'required';
    public static $RULE_EMAIL = 'email';
    public static $RULE_MIN = 'min';
    public static $RULE_MAX = 'max';
    public static $RULE_MATCH = 'match';
    public static $RULE_UNIQUE = 'unique';
    public array $errors = [];
    public function __construct()
    {

    }

    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }


    abstract public function rules(): array;
    public function labels(): array
    {
        return [];
    }
    public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }
    public function validate()
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                $rulename = $rule;
                if (!is_string($rulename)) {
                    $rulename = $rule[0];

                }
                if ($rulename === self::$RULE_REQUIRED && !$value) {
                    $this->addErrorForRule($attribute, self::$RULE_REQUIRED);
                }
                if ($rulename === self::$RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, self::$RULE_EMAIL);
                }
                if ($rulename === self::$RULE_MIN && strlen(trim($value)) < $rule['min']) {
                    $this->addErrorForRule($attribute, self::$RULE_MIN, $rule);
                }
                if ($rulename === self::$RULE_MAX && strlen(trim($value)) > $rule['max']) {
                    $this->addErrorForRule($attribute, self::$RULE_MAX, $rule);
                }
                if ($rulename === self::$RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $rule['match'] = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute, self::$RULE_MATCH, $rule);
                }
                if ($rulename === self::$RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr =:attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorForRule($attribute, self::$RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    }

                }
            }
        }
        return empty ($this->errors);
    }

    private function addErrorForRule(string $attribute, string $rule, array $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
        return $this->errors;
    }

    public function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][] = $message;
    }
    public function errorMessages()
    {
        return [
            self::$RULE_REQUIRED => 'This field is required',
            self::$RULE_EMAIL => 'This field must be a valid email address',
            self::$RULE_MIN => "Min length of this field must be {min}",
            self::$RULE_MAX => 'Max length of this field must be {max}',
            self::$RULE_MATCH => 'This field must be the same as {match}',
            self::$RULE_UNIQUE => 'Record with this {field} already exists',
        ];
    }
    public function hasError($attribute)
    {
        return isset ($this->errors[$attribute]);
    }

    public function getFirstError($attribute)
    {
        return $this->errors[$attribute][0];
    }
}