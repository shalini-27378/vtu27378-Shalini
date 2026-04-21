package com.example.myproject1;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.cloud.netflix.eureka.server.EnableEurekaServer;

@SpringBootApplication
@EnableEurekaServer
public class Week7Task14and15Application {

	public static void main(String[] args) {
		SpringApplication.run(Week7Task14and15Application.class, args);
	}

}
