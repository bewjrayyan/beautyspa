<template x-if="hasSpecialPrice">
    <span
        class="cart-savings-badge badge badge-success"
        x-text="savingsLabel(cartItem.qty)"
    ></span>
</template>
