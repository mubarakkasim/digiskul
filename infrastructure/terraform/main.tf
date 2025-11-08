terraform {
  required_version = ">= 1.0"
  
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  
  backend "s3" {
    bucket = "digiskul-terraform-state"
    key    = "terraform.tfstate"
    region = "us-east-1"
  }
}

provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Project     = "DIGISKUL"
      Environment = var.environment
      ManagedBy   = "Terraform"
    }
  }
}

# VPC
module "vpc" {
  source = "./modules/vpc"
  
  environment = var.environment
  vpc_cidr    = var.vpc_cidr
  
  availability_zones = var.availability_zones
}

# RDS Database
module "database" {
  source = "./modules/database"
  
  environment           = var.environment
  vpc_id                = module.vpc.vpc_id
  private_subnet_ids    = module.vpc.private_subnet_ids
  db_name               = var.db_name
  db_username           = var.db_username
  db_password           = var.db_password
  db_instance_class     = var.db_instance_class
  db_allocated_storage  = var.db_allocated_storage
  
  depends_on = [module.vpc]
}

# ElastiCache Redis
module "redis" {
  source = "./modules/redis"
  
  environment        = var.environment
  vpc_id             = module.vpc.vpc_id
  private_subnet_ids = module.vpc.private_subnet_ids
  node_type          = var.redis_node_type
  
  depends_on = [module.vpc]
}

# S3 Buckets
module "storage" {
  source = "./modules/storage"
  
  environment = var.environment
}

# ECS Cluster
module "ecs" {
  source = "./modules/ecs"
  
  environment         = var.environment
  vpc_id              = module.vpc.vpc_id
  public_subnet_ids   = module.vpc.public_subnet_ids
  private_subnet_ids  = module.vpc.private_subnet_ids
  
  app_image           = var.app_image
  app_port            = 8000
  app_cpu             = var.app_cpu
  app_memory          = var.app_memory
  app_count            = var.app_count
  
  db_host             = module.database.db_endpoint
  db_name             = var.db_name
  db_username         = var.db_username
  db_password         = var.db_password
  
  redis_endpoint      = module.redis.redis_endpoint
  
  s3_bucket           = module.storage.media_bucket_name
  
  depends_on = [module.database, module.redis, module.storage]
}

# CloudFront Distribution
module "cloudfront" {
  source = "./modules/cloudfront"
  
  environment      = var.environment
  alb_dns_name     = module.ecs.alb_dns_name
  s3_bucket_name   = module.storage.media_bucket_name
  domain_name      = var.domain_name
  acm_certificate_arn = var.acm_certificate_arn
}

# Route53 DNS
module "route53" {
  source = "./modules/route53"
  
  domain_name           = var.domain_name
  cloudfront_domain_name = module.cloudfront.cloudfront_domain_name
  cloudfront_zone_id    = module.cloudfront.cloudfront_zone_id
}

# Security Groups
resource "aws_security_group" "alb" {
  name_prefix = "${var.environment}-alb-"
  vpc_id      = module.vpc.vpc_id
  
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "${var.environment}-alb-sg"
  }
}

# Outputs
output "vpc_id" {
  value = module.vpc.vpc_id
}

output "db_endpoint" {
  value     = module.database.db_endpoint
  sensitive = true
}

output "redis_endpoint" {
  value     = module.redis.redis_endpoint
  sensitive = true
}

output "alb_dns_name" {
  value = module.ecs.alb_dns_name
}

output "cloudfront_domain" {
  value = module.cloudfront.cloudfront_domain_name
}

output "route53_domain" {
  value = module.route53.domain_name
}

