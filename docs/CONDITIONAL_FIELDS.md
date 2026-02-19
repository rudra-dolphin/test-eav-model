# Conditional (dependent) fields

Fields can be shown only when another field has a specific value. This is stored in the `attribute_conditions` table and exposed in the form builder with a simple, non-technical UI.

## Example admin UI flow

1. **Form builder** → Edit a form → Add or edit a field (e.g. a text field “Additional notes”).
2. In **“Show this field when (optional)”**:
   - **Parent field**: Choose another field by its label (e.g. “Visit type”).
   - **Trigger value**: Choose one of that field’s options (e.g. “Emergency”).
3. Save the field. No operators, attribute IDs, or condition tables are shown.

Result: “Additional notes” is only shown when “Visit type” equals “Emergency”.

## Example stored condition record

Table: `attribute_conditions`

| id | attribute_id | parent_attribute_id | operator | trigger_value | created_at | updated_at |
|----|--------------|---------------------|----------|---------------|------------|------------|
| 1  | 42           | 10                  | =        | Emergency     | ...        | ...        |

- `attribute_id` 42 = the dependent field (e.g. “Additional notes”).
- `parent_attribute_id` 10 = the field we watch (e.g. “Visit type”).
- `trigger_value` = the option value that triggers visibility (e.g. `"Emergency"` or `"yes"`).

Form structure output still uses the same `showIf` shape: `{ "field": "visit_type", "value": "Emergency" }`, where `field` is the parent attribute’s `name`.

## Backward compatibility

- Attributes **without** a row in `attribute_conditions` are unchanged; existing `attributes.conditional_logic` (if present) is still used for `showIf`.
- Attributes **with** a row in `attribute_conditions` use that row for `showIf`; `conditional_logic` is ignored for that attribute.
