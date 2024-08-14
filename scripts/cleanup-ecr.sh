repos=(cra-portal-backend/blemmyae cra-portal-backend/nginx cra-portal/cerberus cra-portal/nginx elk/filebeat elk/logstash elk/proxy)
region=us-east-2

for repo in ${repos[@]}; do
  echo ${repo}
  images_to_purge=$(aws ecr list-images --region ${region} --repository-name ${repo} --filter "tagStatus=UNTAGGED" --query 'imageIds[*]' --output json)
  if [ -z ${images_to_purge} ]; then
    echo Nothing to cleanup.
  else
    size=$(echo ${images_to_purge} | jq length)
    echo ${size} images to purge
    if [ ${size} -gt 100 ]; then
      # @fixme
      echo Unable to cleanup. API limit "<=100", split into chunks.
    else
      failures=$(aws ecr batch-delete-image --region ${region} --repository-name ${repo} --image-ids "${images_to_purge}" || [])
      echo Failures:
      echo "${failures}" | jq -r '.failures[].imageId.imageDigest' || ''
      echo Failure referers:
      echo "${failures}" | jq -r '.failures[].failureReason' | grep -o 'sha256:[^;]*' | sed -e 's/]//' || ''
    fi
  fi
done
