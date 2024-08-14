while IFS=, read -r col1 col2
do
  JSON=$(jq -n -c --arg appl "$col2" --arg uri "$col1" '{
    "operationName": "EditorialContent",
    "query": "\n query EditorialContent ($slug: ID, $app: [ID]){ \n editorialWithRelatedBlock(\n slug: $slug\n applications: $app\n) { \n id \n databaseId \n blocks { \n __typename \n ...imageGutenbergBlock \n } \n } \n } \n \n fragment imageGutenbergBlock on CoreImageBlock { \n attributes { \n __typename \n ... on \n CoreImageBlockAttributes { \n id \n } \n } \n }",
    "variables": {
      "applications": [
        $appl
      ],
    "slug": $uri
    }
  }')

  response=$(curl -s -H "Content-Type: application/json" -d "${JSON}" http://blemmyae.ddev.site/graphql)

  if [[ $response = *'CoreImageBlockDeprecatedV'* ]]; then
    echo "Found: $col1 | $col2"
  fi
done < editorials.csv
