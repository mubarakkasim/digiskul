# Storage Module
variable "environment" {}

resource "random_id" "bucket_suffix" {
  byte_length = 4
}

resource "aws_s3_bucket" "media" {
  bucket = "${var.environment}-digiskul-media-${random_id.bucket_suffix.hex}"
  
  tags = {
    Name = "${var.environment}-digiskul-media"
  }
}

resource "aws_s3_bucket_versioning" "media" {
  bucket = aws_s3_bucket.media.id
  
  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "media" {
  bucket = aws_s3_bucket.media.id
  
  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_public_access_block" "media" {
  bucket = aws_s3_bucket.media.id
  
  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets  = true
}

resource "aws_s3_bucket" "backups" {
  bucket = "${var.environment}-digiskul-backups-${random_id.bucket_suffix.hex}"
  
  tags = {
    Name = "${var.environment}-digiskul-backups"
  }
}

resource "aws_s3_bucket_versioning" "backups" {
  bucket = aws_s3_bucket.backups.id
  
  versioning_configuration {
    status = "Enabled"
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "backups" {
  bucket = aws_s3_bucket.backups.id
  
  rule {
    id     = "delete_old_backups"
    status = "Enabled"
    
    expiration {
      days = 90
    }
  }
}

output "media_bucket_name" {
  value = aws_s3_bucket.media.id
}

output "backups_bucket_name" {
  value = aws_s3_bucket.backups.id
}

