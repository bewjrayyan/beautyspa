import Errors from "../../../../components/Errors";
import "../../../../components/ProductRating";

Alpine.data(
    "OrderProductReview",
    ({
        productId,
        productName,
        productUrl,
        productImage,
        reviewerName = "",
        existingReview = null,
    }) => ({
        productId,
        productName,
        productUrl,
        productImage,
        reviewerName,
        submittedReview: existingReview,
        reviewForm: {
            reviewer_name: reviewerName,
        },
        addingNewReview: false,
        errors: new Errors(),

        get hasReview() {
            return this.submittedReview !== null;
        },

        buildReviewPayload() {
            const form = this.$refs.reviewForm;
            const payload = {};

            if (form) {
                new FormData(form).forEach((value, key) => {
                    payload[key] = value;
                });

                if (!payload.rating) {
                    const selectedRating = form.querySelector(
                        'input[name="rating"]:checked'
                    );

                    if (selectedRating) {
                        payload.rating = selectedRating.value;
                    }
                }
            } else {
                Object.assign(payload, this.reviewForm);
            }

            if (!payload.rating && this.reviewForm.rating) {
                payload.rating = this.reviewForm.rating;
            }

            const captchaResponse = window.grecaptcha?.getResponse?.();

            if (captchaResponse) {
                payload["g-recaptcha-response"] = captchaResponse;
            }

            return payload;
        },

        addNewReview() {
            this.addingNewReview = true;

            axios
                .post(
                    `/products/${this.productId}/reviews`,
                    this.buildReviewPayload()
                )
                .then((response) => {
                    this.submittedReview = response.data;
                    this.reviewForm = {
                        reviewer_name: this.reviewerName,
                    };
                    this.errors.reset();

                    notify(trans("storefront::product.review_submitted"));
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);

                        const firstError = Object.values(
                            response.data.errors || {}
                        )[0]?.[0];

                        if (firstError) {
                            notify(firstError);
                        }

                        return;
                    }

                    notify(response.data.message);
                })
                .finally(() => {
                    this.addingNewReview = false;

                    if (window.grecaptcha) {
                        grecaptcha.reset();
                    }
                });
        },
    })
);
