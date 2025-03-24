# Technical Context: WordPress Article Rewriter Plugin

## Technologies Used

### Core Technologies
- **PHP 7.4+**: Primary development language
- **JavaScript/ES6+**: Frontend functionality
- **WordPress Plugin API**: Integration with WordPress
- **React**: For Gutenberg components
- **REST API**: For admin and editor communication

### External Services
- **OpenAI API**: For GPT-based rewriting
- **DeepSeek API**: Alternative AI service
- **Envato API**: For purchase code validation

### Libraries & Frameworks
- **WordPress Block Editor API**: For Gutenberg integration
- **TinyMCE API**: For Classic Editor integration
- **WP Background Processing**: For handling batch jobs
- **WP REST API**: For internal communication

## Development Setup

### Requirements
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+ or MariaDB 10.1+
- SSL for secure API communication

### Development Environment
- Local WordPress installation
- API keys for testing services
- Version control with Git
- Composer for dependency management
- npm for frontend asset management

## Technical Constraints

### WordPress Compatibility
- Must work with WordPress 5.8+
- Support for both Gutenberg and Classic Editor
- Follow WordPress coding standards
- Use WordPress hooks and filters appropriately

### API Limitations
- Rate limits on AI services
- Token/character limits for content processing
- Handling service outages gracefully
- Managing API costs effectively

### Performance Considerations
- Minimize impact on page load times
- Efficient batch processing
- Caching where appropriate
- Database optimization for large sites

### Security Requirements
- Secure API key storage
- XSS prevention in editor integrations
- CSRF protection for admin actions
- Data sanitization and validation
- Secure license validation

## Dependencies

### WordPress Core
- Post editing APIs
- REST API infrastructure
- Background processing capabilities
- Settings API

### External APIs
- OpenAI API credentials
- DeepSeek API credentials
- Envato API access

### Database
- Custom tables for:
  - Rewrite history
  - Batch job queue
  - License information

## Technical Debt Considerations
- Plan for API version changes
- Strategy for handling deprecated WordPress functions
- Approach for testing with multiple editor versions
- Maintenance of multiple API connectors
