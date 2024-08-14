generate_class() {
  TARGET_FILE_PATH=$1
  CLASS_NAME=$2
  CONTENT_TYPE=$3
  NAMESPACE=$4
  echo "[status] Executing $CLASS_NAME content type."
  FILE_CONTENT="$(php "scripts/codegen/Codegen.php" "$CLASS_NAME" "$CONTENT_TYPE" "$NAMESPACE")"
  printf "<?php\n\n%s\n" "$FILE_CONTENT" > "$TARGET_FILE_PATH"
  echo "[success] Executed $CLASS_NAME content type."
}

declare -A contentTypes=( [CompanyProfile]="company-profile" [Editorial]=editorial [Landing]=landing [Learning]=learning [Newsletter]=newsletter [People]=people [ProductProfile]="product-profile" [Whitepaper]=whitepaper )
for className in "${!contentTypes[@]}"
do
  generate_class "packages/ct-${contentTypes[$className]}/src/${className}CT.php" "$className" "${contentTypes[${className}]}" "Ct${className}"
done

generate_class "packages/ct-learning/src/SessionCT.php" "Session" "session" "CtLearning"
generate_class "packages/ct-people/src/TestimonialCT.php" "Testimonial" "testimonial" "CtPeople"
generate_class "packages/ct-people/src/ScAwardNomineeCT.php" "ScAwardNominee" "sc_award_nominee" "CtPeople"

declare -A contentTypes=( [PpworksSegment]="ppworks-segment" [PpworksEpisode]="ppworks-episode" [PpworksAnnouncement]="ppworks-announcement" [PpworksArticle]="ppworks-article" [PpworksSponsorProgram]="ppworks-sponsor-prog" )
for className in "${!contentTypes[@]}"
do
  generate_class "packages/blemmyae-ppworks/src/${className}CT.php" "$className" "${contentTypes[$className]}" "BlemmyaePpworks"
done

# TODO: Add generators for all custom taxonomies with their fields.
