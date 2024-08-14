# Additional qaN Env init

1. Init Copilot env
   Re-use already existing test vpc and its public and private subnets

```bash
copilot env init --name %ENV_NAME% --profile default --region us-east-2 \
  --import-vpc-id %SHARED_VPC_ID% \
  --import-public-subnets %SHARED_VPC_SUBNET_PUBLIC_0%,%SHARED_VPC_SUBNET_PUBLIC_1% \
  --import-private-subnets %SHARED_VPC_SUBNET_PRIVATE_0%,%SHARED_VPC_SUBNET_PRIVATE_1% \
  --import-cert-arns arn:aws:acm:us-east-2:361493004146:certificate/205f9094-7c10-448b-b0a5-9befb373a93e \
  --container-insights
```

, where %ENV_NAME% - qa1, qa2, qaN, etc.; default - https://docs.aws.amazon.com/cli/latest/userguide/getting-started-quickstart.html#getting-started-quickstart-existing
For example:

```bash
copilot env init --name qa2 --profile default --region us-east-2 \
  --import-vpc-id vpc-04f7603a382c89ea9 \
  --import-private-subnets subnet-0ad4504e606ca4957,subnet-0ed0805cd74113822 \
  --import-public-subnets subnet-0f988cb3754124e6e,subnet-07a4af403aeac1c17 \
  --import-cert-arns arn:aws:acm:us-east-2:361493004146:certificate/205f9094-7c10-448b-b0a5-9befb373a93e \
  --container-insights
```

2. Run qaN deploy:
   - `copilot svc deploy --name cms --env qaN --tag %IMAGE_TAG%`
   - `copilot svc deploy --name api --env qaN --tag %IMAGE_TAG%`
   - `copilot svc deploy --name proxy --env qaN --tag %IMAGE_TAG%`
3. Update aws secrets, ref: https://us-east-2.console.aws.amazon.com/secretsmanager/home?region=us-east-2#!/listSecrets
    - Add: s3AwsAccessKeyId, s3AwsSecretAccess, s3AwsDefaultRegion; you can copy them from already existing secrets.
4. Update following files:
    - /bitnami/wordpress/wp-config.php
    - /bitnami/wordpress/robots.txt
5. Run deploy again with `--force` param to populate instance env with s3 secrets
6. Import DB using bash script, example: `/opt/copilot/scripts/database/import.sh undefined site-archive-blemmyae-live-1637827234-a4yUU5bUauR49iVm9pHXxQGzvCmlFxVLdygK.gz`
7. Create new GraphQL cdn instance, ref: https://graphcdn.io/dashboard/guvkon/create
8. Point new dns only qaN subdomain to domain generated in step "2.3"
9. Create a new git cerberus branch qaN, example:
    ```git
    git checkout -b qaN
    git push -u upstream qaN
    ```
10. Enjoy your new qaN environment :-)
