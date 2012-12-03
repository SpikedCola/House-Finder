<?php
	class Db {
		public $db;
		
		function __construct() {
			$username = 'root';
			$password = 'spikedcola';
			$database = 'mls';
			$this->db = mysqli_connect('127.0.0.1', $username, $password, $database);
			if (mysqli_error($this->db)) {
				die('sql error');
			}
		}
		
		function user_exists($uniqueId) {
			$query = $this->db->prepare('SELECT * FROM users WHERE user_id = ?');
			
			$query->bind_param('s', $uniqueId);
			
			$query->execute();
			
			if ($query->get_result()) {
				return true;
			}
			
			return false;
		}
		
		function add_user($uniqueId) {
			$query = $this->db->prepare('INSERT INTO users (user_id, date) VALUES (?, UNIX_TIMESTAMP())');
			
			$query->bind_param('s', $uniqueId);
			
			$query->execute();
		}
                
                function getIgnoredListings($user_id) {
                        $ret = array();
                        
			$query = $this->db->prepare('SELECT * FROM ignored_listings WHERE user_id = ?');
			
			$query->bind_param('s', $user_id);
			
			$query->execute();
			
			if ($result = $query->get_result()) {
                                while ($row = $result->fetch_object()) {
                                        $ret[$row->listing_id] = $row->listing_id;

                                }
                        }
                        
                        return $ret;
                }
	}
?>
