# 2026-03-23 23:29 | TOC Link Generalization & Mapping Planning

## What was done
- Analyzed `books_data.json` to understand the relationship between `itemId`, `bookPath`, and `toc.xhtml` links in the Savvas Realize system.
- Identified the URL transformation rule needed to convert metadata links (`lst-eps.savvasrealize.com`) to the user-preferred delivery format (`content-delivery-service.savvasrealize.com`).
- Updated the internal [implementation plan](file:///C:/Users/User/.gemini/antigravity/brain/e9233542-98c1-4db2-9505-64506d262325/implementation_plan.md) and [task list](file:///C:/Users/User/.gemini/antigravity/brain/e9233542-98c1-4db2-9505-64506d262325/task.md) to include automation tools.
- Outlined the logic for `generate_toc_links.js` to extract links for any book on demand.

## What still needs to be done
- Implement `generate_toc_links.js` to automate link extraction and transformation.
- Implement `build_book_map.js` to scan asset folder patterns for the Teacher Edition.
- Execute the scanner for Grade 7 Topic 8 to generate `book_map_grade7.json`.
- Verify the generated links for accuracy.
