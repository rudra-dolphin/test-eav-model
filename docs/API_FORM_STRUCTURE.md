# API Form Structure Response

**Endpoint:** `GET /api/departments/{department}/form-structure`  
**Parameter:** `{department}` = department ID (integer).

---

## Top-level shape

```json
{
  "department": {
    "id": 3,
    "name": "Cardiologiest"
  },
  "sections": [ ... ]
}
```

- **department**: `id` and `name` of the requested department.
- **sections**: Array of section objects. First section is always **Patient Details** (static); rest come from forms in that department, split by Section/Subsection headings.

---

## Section shape

Each section:

```json
{
  "id": "patient_details",
  "title": "Patient Details",
  "fields": [ ... ]
}
```

- **id**: Section id (`patient_details` for static, or heading field name / `{formSlug}_intro` for dynamic).
- **title**: Section title (heading label or empty for intro).
- **fields**: Array of field objects (see below). Fields can be nested via **options[].children** or **children** (trigger-keyed).

---

## Field shape (flat)

- **id**: Machine name (e.g. `patientName`, `medical_history_1`).
- **label**: Display label.
- **type**: One of `text`, `textarea`, `number`, `decimal`, `date`, `radio`, `checkbox`, `dropdown`, `heading`, `heading_sub`.
- **required**: Boolean.
- **errorMessage**: Present when required (e.g. `"Label is required"`).
- **placeholder**: Optional.
- **helpText**: Optional.
- **validation**: Optional (e.g. `{ "min": 1, "max": 120 }`, `{ "rows": 4 }` for textarea).
- **showIf**: Optional; only on child fields: `{ "field": "parent_name", "value": "trigger_value" }`.
- **options**: For radio/checkbox/dropdown: array of option objects (see below).
- **children**: Optional; when field has no options but has conditional children: object keyed by trigger value, e.g. `{ "1": [ ...child fields... ] }`.

---

## Options with inlined children

When a field has **options** and **conditional sub-fields**, each option is:

```json
{
  "value": "diabetes",
  "label": "Type 2 diabetes",
  "children": [
    { "id": "medical_history_1_diabetes_year", "label": "Year", "type": "text", "required": true, "errorMessage": "Year is required" },
    { "id": "medical_history_1_diabetes_month", "label": "Month", "type": "text", "required": false }
  ]
}
```

- **value**, **label**: Option value and label.
- **children**: Array of field objects shown when this option is selected (sub-fields for that option). Empty array if none.

So values related to an option (e.g. diabetes) live **inside** that option as **children**; the outer list is still **sections[].fields**.

---

## Key order in JSON

For fields, keys are ordered so **options** comes last:  
`id`, `label`, `type`, `required`, `placeholder`, `helpText`, `errorMessage`, `validation`, `showIf`, `children`, `options`.

---

## Example (minimal)

```json
{
  "department": { "id": 3, "name": "Cardiology" },
  "sections": [
    {
      "id": "patient_details",
      "title": "Patient Details",
      "fields": [
        { "id": "patientName", "label": "Enter Patient Name", "type": "text", "required": true, "errorMessage": "Name is required" },
        { "id": "age", "label": "Age", "type": "number", "required": true, "errorMessage": "Age is required", "validation": { "min": 1, "max": 120 } },
        { "id": "gender", "label": "Gender", "type": "dropdown", "required": true, "errorMessage": "Gender is required", "options": [ { "value": "male", "label": "Male" }, { "value": "female", "label": "Female" }, { "value": "other", "label": "Other" } ] }
      ]
    },
    {
      "id": "lifestyle",
      "title": "Lifestyle Factors",
      "fields": [
        {
          "id": "medical_history_1",
          "label": "Medical History and Comorbid Conditions",
          "type": "checkbox",
          "required": false,
          "options": [
            { "value": "diabetes", "label": "Type 2 diabetes", "children": [ { "id": "medical_history_1_diabetes_year", "label": "Year", "type": "text", "required": true, "errorMessage": "Year is required" }, { "id": "medical_history_1_diabetes_month", "label": "Month", "type": "text", "required": false } ] },
            { "value": "heartFailure", "label": "Heart Failure", "children": [ { "id": "medical_history_1_heartFailure_year", "label": "Year", "type": "text", "required": true }, { "id": "medical_history_1_heartFailure_month", "label": "Month", "type": "text", "required": false } ] }
          ]
        }
      ]
    }
  ]
}
```

---

## Types

Included field **type** values from the builder: `text`, `textarea`, `number`, `decimal`, `date`, `radio`, `checkbox`, `dropdown`, `heading`, `heading_sub`.
