#!/bin/bash

# DIGISKUL Deployment Script
# Builds Docker image and deploys to ECS

set -e

ENVIRONMENT=${1:-production}
AWS_REGION=${AWS_REGION:-us-east-1}
ECR_REPOSITORY=${ECR_REPOSITORY:-digiskul/app}
ECS_CLUSTER=${ECS_CLUSTER:-production-digiskul-cluster}
ECS_SERVICE=${ECS_SERVICE:-production-digiskul-app}

echo "Building Docker image..."
docker build -t $ECR_REPOSITORY:latest -f infrastructure/docker/Dockerfile .

echo "Logging in to ECR..."
aws ecr get-login-password --region $AWS_REGION | docker login --username AWS --password-stdin $(aws sts get-caller-identity --query Account --output text).dkr.ecr.$AWS_REGION.amazonaws.com

echo "Tagging image..."
docker tag $ECR_REPOSITORY:latest $(aws sts get-caller-identity --query Account --output text).dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPOSITORY:latest

echo "Pushing image to ECR..."
docker push $(aws sts get-caller-identity --query Account --output text).dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPOSITORY:latest

echo "Updating ECS service..."
aws ecs update-service \
  --cluster $ECS_CLUSTER \
  --service $ECS_SERVICE \
  --force-new-deployment \
  --region $AWS_REGION

echo "Deployment initiated. Check ECS console for status."

