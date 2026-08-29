import type { ApplicationFormValues } from "../../types";

interface ApplicationPayloadFieldsProps {
  values: ApplicationFormValues;
  listingId: string;
}

// ApplicationPayloadFields: the apply form's backend contract carrier. The
// wizard is deliberately controlled (structure rules §10 exception) and only
// the active step's fields are mounted, so hidden inputs under the backend's
// field names mirror the whole snapshot ({ values, listingId }) every render —
// at step 3 `new FormData(form)` therefore contains every required value ready
// for submitApplication's cleanup. The CV rides its own always-mounted
// CvFileInput carrier (a file can't ride a hidden input). Leaf: renders only.
export function ApplicationPayloadFields({
  values,
  listingId,
}: ApplicationPayloadFieldsProps) {
  const { personal, education, application } = values;

  return (
    <div className="sr-only" aria-hidden="true">
      <input type="hidden" name="full_name" value={personal.fullName} readOnly />
      <input type="hidden" name="email" value={personal.email} readOnly />
      <input type="hidden" name="phone" value={personal.phone} readOnly />
      <input type="hidden" name="address" value={personal.address} readOnly />
      <input type="hidden" name="city" value={personal.city} readOnly />
      <input type="hidden" name="message" value={personal.description} readOnly />

      <input type="hidden" name="university_id" value={education.university} readOnly />
      <input type="hidden" name="academic_year" value={education.academicYear} readOnly />
      <input type="hidden" name="graduation_year" value={education.graduationYear} readOnly />
      <input type="hidden" name="applicant_type" value={education.status} readOnly />

      <input type="hidden" name="training_id" value={listingId} readOnly />
      <input type="hidden" name="why_interested" value={application.interestReason} readOnly />
      <input type="hidden" name="what_to_learn" value={application.learningGoals} readOnly />

      {application.skills.map((skill) => (
        <input key={skill} type="hidden" name="skills" value={skill} readOnly />
      ))}
    </div>
  );
}