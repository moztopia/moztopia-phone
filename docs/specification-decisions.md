# Current implementation decisions

The verbatim v1.0 specification is preserved in implementation-specification.md. These owner decisions supersede its specified details where they differ.

- 22 November 2026: the owner reiterated that README must remain brief and point to the wiki as the authoritative consumer documentation. The complete consumer/API guide therefore lives in the locally maintained wiki, while source AGENTS remains a concise integration guide. Issue #5 records this clarification. This supersedes the long-README checklist in §27, not the underlying documentation coverage or correctness requirements.
- 22 November 2026: AI agents are the primary consumer documentation audience. Root AGENTS is the first entry point, with task-based authoritative agent instructions in the wiki; no source or upstream knowledge is required. The README directs agents to these guides.
- The specification explicitly prohibits Git tags, GitHub releases and Packagist publication without separate authorization. This holds automatic integration tagging for this task. Source version advances and PR integrations do not publish a package.
- New source starts at 0.0.0 under the shared version policy; the specification's Version 1.0 is the document version, not a claim of a released 1.0.0 package. First core feature and Laravel integrations are 0.1.0 and 0.2.0. Compatibility and delivery tooling prepare 0.3.0 without a tag.
- Runtime constraints retain the specified PHP minor support while enforcing the shared security patch floors: ~8.3.36 || ~8.4.27 || ~8.5.12.

by-dolly
