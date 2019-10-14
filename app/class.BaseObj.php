<?php
    require_once 'DB.php';
    require_once 'interface.baseInterface.php';

    class BaseObj implements BaseInterface{

        protected $attrs = array();
        protected $dbh;
        
        public function __construct(){
            // Establish DB connection here
            $this->dbh = dbConnect();
        }
        
        public function __set($name, $value){
            $this->attrs[$name] = $value;
        }

        public function __get($name){
            return $this->attrs[$name];
        }

        public function __isset($name)
        {
            return isset($this->attrs[$name]);
        }

        /**
         * Set state for an object
         *
         * @access public
         * @param array $data
         * @return void
         * @since method available since release 1.0
         */
        public function setState(Array $data){
            foreach( $data as $name=>$value){
                $this->{$name} = $value;
                // Alternately could write directly to $attrs array
                // $this->attrs[$name] = $value;
                // However using overload methods can allow for
                // additional operations before saving
            }
        }

        /**
         * Create one of an object
         *
         * @access public
         * @param string $title
         * @param int $id
         * @since method available since release 1.0
         */
        public function create($title, $id = 0)
        {
            // TODO: Implement create() method.
        }

        /**
         * Delete one of an object
         *
         * @access public
         * @return void
         * @since method available since release 1.0
         */
        public function delete()
        {
            // TODO: Implement delete() method.
        }

        public function getPath($filename, $dir_name = '', $url = true){
            $this->pathfinder->getPath($filename, $dir_name, $url);
        }
    }