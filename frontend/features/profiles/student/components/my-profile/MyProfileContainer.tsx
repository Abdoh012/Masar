// MyProfileContainer: the /profile page orchestrator. One long scroll of
// independent card sections — the identity hero, then account security, profile
// views, skills & education, contact info, and academic status — composed
// top to bottom with no tab bar.
//
// Four sections are backend-wired: the identity hero (GET /students/me),
// account security (GET /students/me/full-name), profile views
// (GET /students/me/profile-views), and contact info
// (GET /students/me/contact). Each is wrapped in its own Suspense boundary so
// the static sections stream immediately. The rest still render from
// feature-local constants and keep their edits in local client state. Owns
// composition only — each section is its own folder with its own container.
// There is no PageHeader band here on purpose: the identity hero is the page's
// opening panel and carries the page title as its <h1>.

import { Suspense } from "react";

import AccountSecurityContainer from "./account-security/AccountSecurityContainer";
import { AccountSecuritySkeleton } from "./account-security/AccountSecuritySkeleton";
import AcademicStatusContainer from "./academic-status/AcademicStatusContainer";
import { AcademicStatusSkeleton } from "./academic-status/AcademicStatusSkeleton";
import ContactInfoContainer from "./contact-info/ContactInfoContainer";
import { ContactInfoSkeleton } from "./contact-info/ContactInfoSkeleton";
import IdentityContainer from "./identity/IdentityContainer";
import { IdentitySkeleton } from "./identity/IdentitySkeleton";
import ProfileViewsContainer from "./profile-views/ProfileViewsContainer";
import { ProfileViewsSkeleton } from "./profile-views/ProfileViewsSkeleton";
import SkillsEducationContainer from "./skills-education/SkillsEducationContainer";
import { SkillsEducationSkeleton } from "./skills-education/SkillsEducationSkeleton";

export default function MyProfileContainer() {
  return (
    <div className="space-y-6">
      <Suspense fallback={<IdentitySkeleton />}>
        <IdentityContainer />
      </Suspense>

      <Suspense fallback={<AccountSecuritySkeleton />}>
        <AccountSecurityContainer />
      </Suspense>

      <Suspense fallback={<ProfileViewsSkeleton />}>
        <ProfileViewsContainer />
      </Suspense>

      <Suspense fallback={<ContactInfoSkeleton />}>
        <ContactInfoContainer />
      </Suspense>

      <Suspense fallback={<SkillsEducationSkeleton />}>
        <SkillsEducationContainer />
      </Suspense>

      <Suspense fallback={<AcademicStatusSkeleton />}>
        {/* <AcademicStatusContainer /> */}
      </Suspense>
    </div>
  );
}
