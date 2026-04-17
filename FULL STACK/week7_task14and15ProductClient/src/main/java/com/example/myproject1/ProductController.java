package com.example.myproject1;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
public class ProductController {
	
	@GetMapping("Product")
  public String productService() {
	  return" Product Service pages can be loaded here";
	  
  }
}
