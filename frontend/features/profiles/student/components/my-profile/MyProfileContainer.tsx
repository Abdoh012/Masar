// MyProfileContainer: the /profile page orchestrator. One long scroll of
// independent card sections — the identity hero, then account security, profile
// views, skills & education, contact info, and academic status — composed
// top to bottom with no tab bar.
//
// It fetches nothing yet: there is no profile API, so the page renders from
// feature-local constants and every section keeps its edits in local client
// state. Owns composition only — each section is its own folder with its own
// container. There is no PageHeader band here on purpose: the identity hero is
// the page's opening panel and carries the page title as its <h1>. Once a
// profile read lands, this becomes an async server orchestrator that fetches the
// profile, throws on failure (so the route's error.tsx renders), and passes the
// data down; the route page then only needs a Suspense boundary with a
// page-shaped skeleton.

import { PROFILE_IDENTITY } from "./constants";
import AccountSecurityContainer from "./account-security/AccountSecurityContainer";
import AcademicStatusContainer from "./academic-status/AcademicStatusContainer";
import ContactInfoContainer from "./contact-info/ContactInfoContainer";
import IdentityContainer from "./identity/IdentityContainer";
import ProfileViewsContainer from "./profile-views/ProfileViewsContainer";
import SkillsEducationContainer from "./skills-education/SkillsEducationContainer";

export default function MyProfileContainer() {
  return (
    <div className="space-y-6">
      <IdentityContainer profile={PROFILE_IDENTITY} />

      <AccountSecurityContainer />
      <ProfileViewsContainer />
      <SkillsEducationContainer />
      <ContactInfoContainer />
      <AcademicStatusContainer />
    </div>
  );
}
