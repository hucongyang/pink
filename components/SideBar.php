<?php

class SideBar extends CWidget {

    public $view = 'sidebar';
    public $controller;
    public $action;
    public $type = 0;
    public $types = array();

    public function run() {
        $this->render($this->view, array(
            "action" => $this->action,
            "controller" => $this->controller,
            "type" => $this->type,
            "types" => $this->types,
        ));
    }
}