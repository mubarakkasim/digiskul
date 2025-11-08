# DIGISKUL AWS Infrastructure - Complete Setup

This directory contains Terraform configuration for deploying DIGISKUL on AWS.

## Architecture Overview

- **VPC**: Custom VPC with public and private subnets across multiple AZs
- **RDS**: MySQL database in private subnets with Multi-AZ for high availability
- **ElastiCache**: Redis cluster for caching and queues
- **ECS Fargate**: Containerized Laravel application
- **Application Load Balancer**: Distributes traffic to ECS tasks
- **CloudFront**: CDN for static assets and API caching
- **S3**: Media storage and backups
- **Route53**: DNS management

## Prerequisites

1. AWS CLI configured with appropriate permissions
2. Terraform >= 1.0 installed
3. S3 bucket for Terraform state (create manually first)
4. Domain name and ACM certificate (optional for HTTPS)

## Quick Start

### 1. Create S3 bucket for Terraform state:
```bash
aws s3 mb s3://digiskul-terraform-state --region us-east-1
aws s3api put-bucket-versioning \
  --bucket digiskul-terraform-state \
  --versioning-configuration Status=Enabled
```

### 2. Create `terraform.tfvars`:
```hcl
aws_region = "us-east-1"
environment = "production"
db_username = "admin"
db_password = "your-secure-password-here"
domain_name = "digiskul.app"
acm_certificate_arn = "arn:aws:acm:us-east-1:ACCOUNT:certificate/CERT_ID"
```

### 3. Initialize Terraform:
```bash
cd infrastructure/terraform
terraform init
```

### 4. Plan deployment:
```bash
terraform plan -out=tfplan
```

### 5. Apply configuration:
```bash
terraform apply tfplan
```

## Module Structure

```
terraform/
├── main.tf                 # Main configuration
├── variables.tf            # Variable definitions
├── outputs.tf              # Output values
└── modules/
    ├── vpc/                # VPC and networking
    ├── database/           # RDS MySQL
    ├── redis/              # ElastiCache Redis
    ├── storage/            # S3 buckets
    ├── ecs/                # ECS Fargate cluster
    ├── cloudfront/         # CloudFront CDN
    └── route53/            # DNS configuration
```

## Variables

See `variables.tf` for all available variables. Key variables:

- `aws_region`: AWS region (default: us-east-1)
- `environment`: Environment name (default: production)
- `db_username`: Database master username
- `db_password`: Database master password (sensitive)
- `domain_name`: Domain name for the application
- `acm_certificate_arn`: ACM certificate ARN for HTTPS

## Outputs

After deployment, Terraform outputs:
- `vpc_id`: VPC ID
- `db_endpoint`: Database endpoint
- `redis_endpoint`: Redis endpoint
- `alb_dns_name`: Application Load Balancer DNS name
- `cloudfront_domain`: CloudFront distribution domain
- `route53_domain`: Route53 domain name

## Cost Estimation

Approximate monthly costs (us-east-1):
- RDS (db.t3.medium, Multi-AZ): ~$150
- ECS Fargate (2 tasks, 1GB, 0.5 CPU): ~$50
- ElastiCache (cache.t3.micro, Multi-AZ): ~$15
- ALB: ~$20
- CloudFront: ~$10
- S3: ~$5
- Data transfer: Variable

**Total: ~$250-300/month** (excluding data transfer)

## Security Features

- ✅ All resources in private subnets where possible
- ✅ Security groups restrict access
- ✅ Encryption at rest for RDS and S3
- ✅ SSL/TLS for all connections
- ✅ Secrets Manager for sensitive data
- ✅ VPC isolation

## Maintenance

- **Database backups**: Automated daily, retained for 7 days
- **Logs**: CloudWatch Logs, retained for 7 days
- **Monitoring**: CloudWatch metrics and container insights enabled
- **Updates**: Use deployment script to update ECS service

## Scaling

- ECS service auto-scaling can be configured
- RDS can be scaled vertically
- CloudFront handles CDN caching automatically
- Multi-AZ for high availability

## Deployment

Use the deployment script to build and deploy:
```bash
chmod +x infrastructure/scripts/deploy.sh
./infrastructure/scripts/deploy.sh production
```

## Troubleshooting

### Common Issues

1. **Terraform state lock**: Wait for other operations to complete
2. **IAM permissions**: Ensure AWS credentials have sufficient permissions
3. **Subnet availability**: Check availability zones in your region
4. **Certificate**: ACM certificate must be in us-east-1 for CloudFront

## Additional Resources

- [Terraform AWS Provider Documentation](https://registry.terraform.io/providers/hashicorp/aws/latest/docs)
- [ECS Best Practices](https://docs.aws.amazon.com/AmazonECS/latest/bestpracticesguide/intro.html)
- [RDS Best Practices](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_BestPractices.html)

