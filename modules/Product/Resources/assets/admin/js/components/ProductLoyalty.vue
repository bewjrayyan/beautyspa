<template>
    <div class="box-header">
        <h5>
            {{ trans("loyalty::products.group") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="form-group row">
            <label
                for="loyalty-bonus-points"
                class="col-sm-12 control-label text-left"
            >
                {{ trans("loyalty::products.bonus_points") }}
            </label>

            <div class="col-sm-12">
                <input
                    type="number"
                    min="0"
                    step="1"
                    name="loyalty_bonus_points"
                    id="loyalty-bonus-points"
                    class="form-control"
                    v-model.number="form.loyalty_bonus_points"
                />

                <span class="help-block">
                    {{ trans("loyalty::products.bonus_points_help") }}
                </span>

                <span
                    class="help-block text-red"
                    v-if="errors.has('loyalty_bonus_points')"
                    v-text="errors.get('loyalty_bonus_points')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="loyalty-earn-multiplier"
                class="col-sm-12 control-label text-left"
            >
                {{ trans("loyalty::products.earn_multiplier") }}
            </label>

            <div class="col-sm-12">
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    name="loyalty_earn_multiplier"
                    id="loyalty-earn-multiplier"
                    class="form-control"
                    v-model.number="form.loyalty_earn_multiplier"
                />

                <span class="help-block">
                    {{ trans("loyalty::products.earn_multiplier_help") }}
                </span>

                <span
                    class="help-block text-red"
                    v-if="errors.has('loyalty_earn_multiplier')"
                    v-text="errors.get('loyalty_earn_multiplier')"
                ></span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "../composables/useForm";

const { form, errors } = useForm();
</script>
