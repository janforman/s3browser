S3 compatible web browser
=========================
A lightweight, self-hosted web interface that provides file access capabilities across any storage service compatible with the Amazon S3 API. Whether you're using AWS S3, RustFS, MinIO, Cloudflare R2, Backblaze B2, DigitalOcean Spaces, or other S3-compatible providers, this tool delivers a familiar, folder-like experience directly in your browser without requiring desktop clients or third-party sync agents.

⚙️ Architecture: JavaScript Frontend + PHP Backend
JavaScript Frontend: Delivers a responsive, modern UI with fast client-side interactions, lazy loading, and intuitive drag-and-drop uploads. Built to work seamlessly across desktop and mobile browsers.
PHP Backend: Handles secure authentication, metadata processing, S3 API routing, and server-side operations. Leverages PHP's mature ecosystem for easy deployment on standard LAMP/LEMP stacks, shared hosting environments, or containerized setups without requiring Node.js build pipelines or complex runtime dependencies.
📁 Key Features
Fake Directories Support: Amazon S3 uses a flat key namespace rather than true hierarchical folders. This tool intelligently simulates directory structures in the UI while translating all operations into efficient prefix-based object listings under the hood. Users get intuitive folder navigation without sacrificing S3's native performance or storage model.
Integrated Media Player: Stream audio and video files directly from your bucket using a built-in HTML5 player. Supports common formats (MP4, WebM, MP3, WAV) and leverages HTTP range requests for smooth playback of large files over slow connections.
Picture Browser & Gallery: A dedicated image viewer featuring auto-generated thumbnails, pinch-to-zoom, slideshow mode, EXIF metadata display, and batch selection tools. Optimized for fast rendering via lazy loading and direct object URLs or signed access links.

🔐 Security & Compatibility Note: S3 Signature Version 4 Support
⚠️ AWS Signature Version 4 is fully supported.
Most modern S3-compatible providers now require or strongly recommend Signature V4 authentication, which replaces the deprecated Signature V2. This application generates valid V4 signatures using HMAC-SHA256, enabling secure, time-bound request authentication and proper handling of cross-region endpoints, IAM policies, and presigned URLs for direct browser uploads/downloads. Ensure your backend credentials are configured with appropriate permissions to generate V4-signed requests, especially when interacting with providers that enforce strict security policies or disable legacy signing methods.

💡 Ideal For
Self-hosted media libraries & backup portals
Developer tooling & CI/CD artifact viewers
Multi-tenant S3 management dashboards
Environments where lightweight, browser-native access is preferred over proprietary cloud consoles
