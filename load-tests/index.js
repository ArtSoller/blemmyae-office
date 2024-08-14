import { checkEditorial, checkLanding, checkTypename } from "./lib/tests.js";

export default () => {
    checkTypename();
    checkEditorial();
    checkLanding();
};
