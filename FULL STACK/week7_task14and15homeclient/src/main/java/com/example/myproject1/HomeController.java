package com.example.myproject1;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
public class HomeController {
	
	
	@GetMapping("home")
	public String msg() {
		return "I'm the Home Service.You can create a Home page and display in your browser";
	}

}
