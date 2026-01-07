import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    const blockProps = useBlockProps({
        style: {
            maxWidth: '680px',
            margin: '40px auto'
        }
    });

    return (
        <div {...blockProps}>
            <div style={{
                background: '#ffffff',
                borderRadius: '16px',
                boxShadow: '0 10px 40px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.06)',
                overflow: 'hidden',
                border: '2px dashed #667eea'
            }}>
                <div style={{
                    height: '6px',
                    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                }}></div>

                <div style={{ padding: '32px 40px' }}>
                    <div style={{
                        textAlign: 'center',
                        marginBottom: '24px',
                        padding: '16px',
                        background: 'linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%)',
                        borderRadius: '10px',
                        border: '1px solid rgba(102, 126, 234, 0.2)'
                    }}>
                        <p style={{
                            margin: 0,
                            color: '#5a67d8',
                            fontWeight: '600',
                            fontSize: '14px'
                        }}>
                            ✨ {__('Editor Preview - Form will be interactive on the frontend', 'wp-form-plugin')}
                        </p>
                    </div>

                    <div style={{ marginBottom: '24px' }}>
                        <label style={{
                            display: 'block',
                            marginBottom: '10px',
                            fontWeight: '600',
                            fontSize: '14px',
                            color: '#2d3748'
                        }}>
                            {__('Name', 'wp-form-plugin')} <span style={{ color: '#e53e3e' }}>*</span>
                        </label>
                        <input
                            type="text"
                            disabled
                            placeholder={__('Enter your full name', 'wp-form-plugin')}
                            style={{
                                width: '100%',
                                padding: '14px 16px',
                                border: '2px solid #e2e8f0',
                                borderRadius: '10px',
                                fontSize: '15px',
                                backgroundColor: '#f7fafc',
                                boxSizing: 'border-box'
                            }}
                        />
                    </div>

                    <div style={{ marginBottom: '24px' }}>
                        <label style={{
                            display: 'block',
                            marginBottom: '10px',
                            fontWeight: '600',
                            fontSize: '14px',
                            color: '#2d3748'
                        }}>
                            {__('Email', 'wp-form-plugin')} <span style={{ color: '#e53e3e' }}>*</span>
                        </label>
                        <input
                            type="email"
                            disabled
                            placeholder={__('your.email@example.com', 'wp-form-plugin')}
                            style={{
                                width: '100%',
                                padding: '14px 16px',
                                border: '2px solid #e2e8f0',
                                borderRadius: '10px',
                                fontSize: '15px',
                                backgroundColor: '#f7fafc',
                                boxSizing: 'border-box'
                            }}
                        />
                    </div>

                    <div style={{ marginBottom: '24px' }}>
                        <label style={{
                            display: 'block',
                            marginBottom: '10px',
                            fontWeight: '600',
                            fontSize: '14px',
                            color: '#2d3748'
                        }}>
                            {__('Message', 'wp-form-plugin')} <span style={{ color: '#e53e3e' }}>*</span>
                        </label>
                        <textarea
                            disabled
                            placeholder={__('Tell us what\'s on your mind...', 'wp-form-plugin')}
                            rows="5"
                            style={{
                                width: '100%',
                                padding: '14px 16px',
                                border: '2px solid #e2e8f0',
                                borderRadius: '10px',
                                fontSize: '15px',
                                backgroundColor: '#f7fafc',
                                resize: 'vertical',
                                lineHeight: '1.6',
                                boxSizing: 'border-box'
                            }}
                        />
                    </div>

                    <button
                        disabled
                        style={{
                            width: '100%',
                            padding: '16px 32px',
                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            color: 'white',
                            border: 'none',
                            borderRadius: '10px',
                            fontSize: '16px',
                            fontWeight: '600',
                            cursor: 'not-allowed',
                            opacity: '0.7',
                            marginTop: '8px'
                        }}
                    >
                        {__('Send Message', 'wp-form-plugin')}
                    </button>
                </div>
            </div>
        </div>
    );
}
