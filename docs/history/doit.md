You are an AI system generating valid JSON field data for a database table named schools.

Rules:

This table stores stable school profile data only (no academic year data).

Do not include users or contacts as full objects — only reference IDs where needed.

Output JSON only, no explanation.

Fields must be realistic and suitable for production.

Include support for school logo upload and logo change tracking.

Required JSON structure:

{
  "id": integer,
  "school_code": string,
  "name_official": string,
  "name_short": string,
  "school_type": "public | private | international",
  "education_levels": ["kg", "primary", "middle", "secondary"],
  "gender_type": "boys | girls | mixed",
  "ownership_type": "government | private",
  "authority": string,
  "year_established": integer,
  "status": "active | inactive",

  "contact": {
    "phone_primary": string,
    "phone_secondary": string | null,
    "email_official": string,
    "website": string | null
  },

  "address": {
    "country": string,
    "region": string,
    "city": string,
    "district": string,
    "street_address": string,
    "postal_code": string | null,
    "latitude": number | null,
    "longitude": number | null
  },

  "logo": {
    "current_logo_url": string | null,
    "logo_version": integer,
    "last_changed_at": "YYYY-MM-DD HH:MM:SS" | null
  },

  "created_at": "YYYY-MM-DD HH:MM:SS",
  "updated_at": "YYYY-MM-DD HH:MM:SS"
}


Generate one realistic school record.

🔁 Optional: Prompt for Logo Change Only

Use this when updating the logo without touching other data.

Prompt:

Generate a JSON payload to update a school logo.

Increment logo_version

Update current_logo_url

Update last_changed_at

Do not modify any other fields

💡 Backend Tip (important)

In Laravel, this works best if:

logo.current_logo_url → stored file path

logo_version → helps with cache busting

last_changed_at → audit + sync systems