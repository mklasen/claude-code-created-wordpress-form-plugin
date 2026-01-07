import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    const blockProps = useBlockProps.save();

    return (
        <div {...blockProps}>
            <form className="wp-form-plugin-contact-form">
                <div className="wp-form-plugin-form-inner">
                    {/* Honeypot field - hidden from humans, visible to bots */}
                    <input
                        type="text"
                        name="website"
                        tabIndex="-1"
                        autoComplete="off"
                        style={{ position: 'absolute', left: '-9999px', width: '1px', height: '1px' }}
                        aria-hidden="true"
                    />
                    <div className="wp-form-plugin-field">
                        <label htmlFor="wp-form-name">Name</label>
                        <input
                            type="text"
                            id="wp-form-name"
                            name="name"
                            required
                            placeholder="Enter your full name"
                        />
                        <div className="wp-form-plugin-field-error"></div>
                    </div>
                    <div className="wp-form-plugin-field">
                        <label htmlFor="wp-form-email">Email</label>
                        <input
                            type="email"
                            id="wp-form-email"
                            name="email"
                            required
                            placeholder="your.email@example.com"
                        />
                        <div className="wp-form-plugin-field-error"></div>
                    </div>
                    <div className="wp-form-plugin-field">
                        <label htmlFor="wp-form-message">Message</label>
                        <textarea
                            id="wp-form-message"
                            name="message"
                            required
                            rows="5"
                            placeholder="Tell us what's on your mind..."
                        ></textarea>
                        <div className="wp-form-plugin-field-error"></div>
                    </div>
                    <button type="submit" className="wp-form-plugin-submit">
                        <span className="button-text">Send Message</span>
                    </button>
                    <div className="wp-form-plugin-message"></div>
                </div>
            </form>
        </div>
    );
}
