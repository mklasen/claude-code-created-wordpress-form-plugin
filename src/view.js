document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.wp-form-plugin-contact-form');

    forms.forEach((form) => {
        const fields = {
            name: form.querySelector('[name="name"]'),
            email: form.querySelector('[name="email"]'),
            message: form.querySelector('[name="message"]')
        };

        const submitButton = form.querySelector('.wp-form-plugin-submit');
        const buttonText = submitButton.querySelector('.button-text');
        const messageDiv = form.querySelector('.wp-form-plugin-message');

        // Real-time validation
        const validateField = (field, rules) => {
            const value = field.value.trim();
            const fieldContainer = field.closest('.wp-form-plugin-field');
            const errorDiv = fieldContainer.querySelector('.wp-form-plugin-field-error');

            let error = '';

            if (rules.required && !value) {
                error = `${field.name.charAt(0).toUpperCase() + field.name.slice(1)} is required`;
            } else if (rules.email && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    error = 'Please enter a valid email address';
                }
            } else if (rules.minLength && value.length < rules.minLength) {
                error = `Please enter at least ${rules.minLength} characters`;
            }

            if (error) {
                field.classList.add('error');
                field.classList.remove('success');
                errorDiv.textContent = error;
                errorDiv.classList.add('visible');
                return false;
            } else if (value) {
                field.classList.remove('error');
                field.classList.add('success');
                errorDiv.classList.remove('visible');
                return true;
            }

            field.classList.remove('error', 'success');
            errorDiv.classList.remove('visible');
            return true;
        };

        // Add blur event listeners for real-time validation
        fields.name.addEventListener('blur', () => {
            validateField(fields.name, { required: true, minLength: 2 });
        });

        fields.email.addEventListener('blur', () => {
            validateField(fields.email, { required: true, email: true });
        });

        fields.message.addEventListener('blur', () => {
            validateField(fields.message, { required: true, minLength: 10 });
        });

        // Add input listeners to remove error state when typing
        Object.values(fields).forEach(field => {
            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    const fieldContainer = field.closest('.wp-form-plugin-field');
                    const errorDiv = fieldContainer.querySelector('.wp-form-plugin-field-error');
                    field.classList.remove('error');
                    errorDiv.classList.remove('visible');
                }
            });
        });

        // Form submission
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Validate all fields
            const isNameValid = validateField(fields.name, { required: true, minLength: 2 });
            const isEmailValid = validateField(fields.email, { required: true, email: true });
            const isMessageValid = validateField(fields.message, { required: true, minLength: 10 });

            if (!isNameValid || !isEmailValid || !isMessageValid) {
                // Shake the form
                form.style.animation = 'none';
                setTimeout(() => {
                    form.style.animation = '';
                }, 10);
                return;
            }

            const formData = new FormData(form);

            // Show loading state
            submitButton.disabled = true;
            submitButton.classList.add('loading');
            buttonText.style.opacity = '0';
            messageDiv.classList.remove('visible', 'success', 'error');

            try {
                const response = await fetch(wpFormPlugin.restUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wpFormPlugin.nonce
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        email: formData.get('email'),
                        message: formData.get('message'),
                        website: formData.get('website') || '', // Honeypot field
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    // Success!
                    messageDiv.textContent = data.message || 'Thank you! Your message has been sent successfully.';
                    messageDiv.classList.add('visible', 'success');

                    // Reset form
                    form.reset();

                    // Remove success classes from fields
                    Object.values(fields).forEach(field => {
                        field.classList.remove('success', 'error');
                    });

                    // Celebrate with a subtle animation
                    messageDiv.style.animation = 'none';
                    setTimeout(() => {
                        messageDiv.style.animation = '';
                    }, 10);
                } else {
                    throw new Error(data.message || 'Submission failed');
                }
            } catch (error) {
                messageDiv.textContent = error.message || 'Oops! Something went wrong. Please try again.';
                messageDiv.classList.add('visible', 'error');
            } finally {
                // Reset button state
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
                buttonText.style.opacity = '1';
            }
        });
    });
});
