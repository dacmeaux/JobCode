<?php
interface BaseInterface{
    public function create($title, $id = 0);
    public function delete();
    public function setState(Array $data);
}
