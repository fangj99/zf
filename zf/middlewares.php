<?php

return [
	'body' => function($schema){
		if($errors = $this->validator->validate($this->body->asRaw(), $schema)){
			$this->emit(\zf\EVENT_VALIDATION_ERROR, ['errors'=> $errors]);
			$this->errors = $errors;
			$this->body = null;
		}else{
			$this->body = $this->body->asRaw();
		}
	},
	'mockup' => function($mockup){
		if($this->config->get('mockup'))
			return file_get_contents($this->resolvePath(explode('.', $mockup)));
	},
];
