import type { CvFileState, PersonalInfoValues } from "../../types";
import { ApplicationField } from "./ApplicationField";
import { CvUploadField } from "./CvUploadField";
import { PERSONAL_INFO_FIELDS } from "./constants";

interface PersonalInfoFieldsProps {
  values: PersonalInfoValues;
  cvFile: CvFileState | null;
  onFieldChange: (field: keyof PersonalInfoValues, value: string) => void;
  onCvSelect: (file: File) => void;
  onCvRemove: () => void;
}

// PersonalInfoFields: step 1 of the apply wizard — contact details + CV upload.
// Composes the shared ApplicationField leaf (two-up on sm+ for the short fields,
// full width for the long ones) and the CvUploadField. Leaf: receives the
// step's values and narrow change handlers from the orchestrator, renders only.
export function PersonalInfoFields({
  values,
  cvFile,
  onFieldChange,
  onCvSelect,
  onCvRemove,
}: PersonalInfoFieldsProps) {
  return (
    <div className="space-y-5">
      <div className="grid gap-5 sm:grid-cols-2">
        <ApplicationField
          id="fullName"
          name="fullName"
          label={PERSONAL_INFO_FIELDS.fullName.label}
          type={PERSONAL_INFO_FIELDS.fullName.type}
          placeholder={PERSONAL_INFO_FIELDS.fullName.placeholder}
          autoComplete={PERSONAL_INFO_FIELDS.fullName.autoComplete}
          value={values.fullName}
          onChange={(value) => onFieldChange("fullName", value)}
          required
        />

        <ApplicationField
          id="email"
          name="email"
          label={PERSONAL_INFO_FIELDS.email.label}
          type={PERSONAL_INFO_FIELDS.email.type}
          placeholder={PERSONAL_INFO_FIELDS.email.placeholder}
          autoComplete={PERSONAL_INFO_FIELDS.email.autoComplete}
          value={values.email}
          onChange={(value) => onFieldChange("email", value)}
          required
        />
      </div>

      <ApplicationField
        id="description"
        name="description"
        label={PERSONAL_INFO_FIELDS.description.label}
        type="textarea"
        placeholder={PERSONAL_INFO_FIELDS.description.placeholder}
        value={values.description}
        onChange={(value) => onFieldChange("description", value)}
        optional
      />

      <div className="grid gap-5 sm:grid-cols-2">
        <ApplicationField
          id="phone"
          name="phone"
          label={PERSONAL_INFO_FIELDS.phone.label}
          type={PERSONAL_INFO_FIELDS.phone.type}
          placeholder={PERSONAL_INFO_FIELDS.phone.placeholder}
          autoComplete={PERSONAL_INFO_FIELDS.phone.autoComplete}
          value={values.phone}
          onChange={(value) => onFieldChange("phone", value)}
          required
        />

        <ApplicationField
          id="city"
          name="city"
          label={PERSONAL_INFO_FIELDS.city.label}
          type={PERSONAL_INFO_FIELDS.city.type}
          placeholder={PERSONAL_INFO_FIELDS.city.placeholder}
          autoComplete={PERSONAL_INFO_FIELDS.city.autoComplete}
          value={values.city}
          onChange={(value) => onFieldChange("city", value)}
          required
        />
      </div>

      <ApplicationField
        id="address"
        name="address"
        label={PERSONAL_INFO_FIELDS.address.label}
        type={PERSONAL_INFO_FIELDS.address.type}
        placeholder={PERSONAL_INFO_FIELDS.address.placeholder}
        autoComplete={PERSONAL_INFO_FIELDS.address.autoComplete}
        value={values.address}
        onChange={(value) => onFieldChange("address", value)}
        required
      />

      <CvUploadField file={cvFile} onSelect={onCvSelect} onRemove={onCvRemove} />
    </div>
  );
}