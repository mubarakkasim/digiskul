# CloudFront Module
variable "environment" {}
variable "alb_dns_name" {}
variable "s3_bucket_name" {}
variable "domain_name" {}
variable "acm_certificate_arn" {}

data "aws_s3_bucket" "media" {
  bucket = var.s3_bucket_name
}

resource "aws_cloudfront_origin_access_identity" "media" {
  comment = "OAI for ${var.environment} media bucket"
}

resource "aws_cloudfront_distribution" "main" {
  enabled             = true
  is_ipv6_enabled     = true
  comment             = "DIGISKUL CloudFront Distribution"
  default_root_object = "index.html"
  
  aliases = var.acm_certificate_arn != "" ? [var.domain_name] : []
  
  origin {
    domain_name = var.alb_dns_name
    origin_id   = "alb-origin"
    
    custom_origin_config {
      http_port              = 80
      https_port             = 443
      origin_protocol_policy = "http-only"
      origin_ssl_protocols   = ["TLSv1.2"]
    }
  }
  
  origin {
    domain_name = data.aws_s3_bucket.media.bucket_regional_domain_name
    origin_id   = "s3-media-origin"
    
    s3_origin_config {
      origin_access_identity = aws_cloudfront_origin_access_identity.media.cloudfront_access_identity_path
    }
  }
  
  default_cache_behavior {
    allowed_methods  = ["DELETE", "GET", "HEAD", "OPTIONS", "PATCH", "POST", "PUT"]
    cached_methods   = ["GET", "HEAD"]
    target_origin_id = "alb-origin"
    
    forwarded_values {
      query_string = true
      headers      = ["Host", "Authorization"]
      
      cookies {
        forward = "all"
      }
    }
    
    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 0
    default_ttl            = 3600
    max_ttl                = 86400
    compress               = true
  }
  
  ordered_cache_behavior {
    path_pattern     = "/media/*"
    allowed_methods  = ["GET", "HEAD"]
    cached_methods   = ["GET", "HEAD"]
    target_origin_id = "s3-media-origin"
    
    forwarded_values {
      query_string = false
      headers      = ["Origin"]
      
      cookies {
        forward = "none"
      }
    }
    
    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 0
    default_ttl            = 86400
    max_ttl                = 31536000
    compress               = true
  }
  
  restrictions {
    geo_restriction {
      restriction_type = "none"
    }
  }
  
  viewer_certificate {
    cloudfront_default_certificate = var.acm_certificate_arn == ""
    acm_certificate_arn            = var.acm_certificate_arn != "" ? var.acm_certificate_arn : null
    ssl_support_method             = var.acm_certificate_arn != "" ? "sni-only" : null
    minimum_protocol_version        = var.acm_certificate_arn != "" ? "TLSv1.2_2021" : null
  }
  
  tags = {
    Name = "${var.environment}-digiskul-cloudfront"
  }
}

resource "aws_s3_bucket_policy" "media" {
  bucket = data.aws_s3_bucket.media.id
  
  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Principal = {
          AWS = aws_cloudfront_origin_access_identity.media.iam_arn
        }
        Action   = "s3:GetObject"
        Resource = "${data.aws_s3_bucket.media.arn}/*"
      }
    ]
  })
}

output "cloudfront_domain_name" {
  value = aws_cloudfront_distribution.main.domain_name
}

output "cloudfront_zone_id" {
  value = aws_cloudfront_distribution.main.hosted_zone_id
}

