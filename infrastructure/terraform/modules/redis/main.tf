# Redis Module
variable "environment" {}
variable "vpc_id" {}
variable "private_subnet_ids" {}
variable "node_type" {}

resource "aws_security_group" "redis" {
  name_prefix = "${var.environment}-redis-"
  vpc_id      = var.vpc_id
  
  ingress {
    from_port   = 6379
    to_port     = 6379
    protocol    = "tcp"
    cidr_blocks = [data.aws_vpc.main.cidr_block]
  }
  
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  tags = {
    Name = "${var.environment}-redis-sg"
  }
}

data "aws_vpc" "main" {
  id = var.vpc_id
}

resource "aws_elasticache_subnet_group" "main" {
  name       = "${var.environment}-redis-subnet-group"
  subnet_ids = var.private_subnet_ids
}

resource "aws_elasticache_replication_group" "main" {
  replication_group_id         = "${var.environment}-digiskul-redis"
  description                  = "Redis cluster for DIGISKUL"
  node_type                    = var.node_type
  port                         = 6379
  parameter_group_name         = "default.redis7"
  num_cache_clusters           = 2
  automatic_failover_enabled   = true
  multi_az_enabled             = true
  subnet_group_name            = aws_elasticache_subnet_group.main.name
  security_group_ids           = [aws_security_group.redis.id]
  
  at_rest_encryption_enabled = true
  transit_encryption_enabled = true
  
  tags = {
    Name = "${var.environment}-digiskul-redis"
  }
}

output "redis_endpoint" {
  value = aws_elasticache_replication_group.main.primary_endpoint_address
}

output "redis_port" {
  value = aws_elasticache_replication_group.main.port
}

