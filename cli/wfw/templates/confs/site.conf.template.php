{
	"server" : {
		"databases": {
			"default": {
				"host": "<?php echo $this->_dbHost; ?>",
				"database": "<?php echo $this->_dbName; ?>",
				"login": "<?php echo $this->_dbLogin; ?>",
				"password": "<?php echo $this->_dbPassword ?>"
			}
		},
		"msserver" : {
			"addr" : "<?php echo $this->_mssAddr; ?>",
			"db" : "<?php echo $this->_mssDb; ?>",
			"login" : "<?php echo $this->_mssLogin; ?>",
			"password" : "<?php echo $this->_mssPassword; ?>"
		},
		"display_loading_time" : false
	}
}