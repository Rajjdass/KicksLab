const User = require('../models/User');
const Shoe = require('../models/Shoe');
const { validationResult } = require('express-validator');

// @desc    Get user's cart
// @route   GET /api/cart
// @access  Private
const getCart = async (req, res) => {
    try {
        const user = await User.findById(req.user._id)
            .populate('cart.shoe', 'name brand price images');

        res.json(user.cart);
    } catch (error) {
        console.error('Get cart error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Add item to cart
// @route   POST /api/cart
// @access  Private
const addToCart = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { shoeId, quantity, size } = req.body;

        // Check if shoe exists and is active
        const shoe = await Shoe.findById(shoeId);
        if (!shoe || !shoe.isActive) {
            return res.status(404).json({ message: 'Shoe not found' });
        }

        // Check if size is available
        const sizeStock = shoe.sizes.find(s => s.size === size);
        if (!sizeStock || sizeStock.stock < quantity) {
            return res.status(400).json({ message: 'Size not available or insufficient stock' });
        }

        const user = await User.findById(req.user._id);

        // Check if item already exists in cart
        const existingItemIndex = user.cart.findIndex(
            item => item.shoe.toString() === shoeId && item.size === size
        );

        if (existingItemIndex > -1) {
            // Update quantity
            user.cart[existingItemIndex].quantity += quantity;
        } else {
            // Add new item
            user.cart.push({ shoe: shoeId, quantity, size });
        }

        await user.save();

        // Populate the cart with shoe details
        await user.populate('cart.shoe', 'name brand price images');

        res.json(user.cart);
    } catch (error) {
        console.error('Add to cart error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Update cart item quantity
// @route   PUT /api/cart/:itemId
// @access  Private
const updateCartItem = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { quantity } = req.body;
        const user = await User.findById(req.user._id);

        const itemIndex = user.cart.findIndex(
            item => item._id.toString() === req.params.itemId
        );

        if (itemIndex === -1) {
            return res.status(404).json({ message: 'Item not found in cart' });
        }

        // Check stock availability
        const shoe = await Shoe.findById(user.cart[itemIndex].shoe);
        const sizeStock = shoe.sizes.find(s => s.size === user.cart[itemIndex].size);

        if (sizeStock.stock < quantity) {
            return res.status(400).json({ message: 'Insufficient stock' });
        }

        user.cart[itemIndex].quantity = quantity;
        await user.save();

        await user.populate('cart.shoe', 'name brand price images');

        res.json(user.cart);
    } catch (error) {
        console.error('Update cart error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Remove item from cart
// @route   DELETE /api/cart/:itemId
// @access  Private
const removeFromCart = async (req, res) => {
    try {
        const user = await User.findById(req.user._id);

        user.cart = user.cart.filter(
            item => item._id.toString() !== req.params.itemId
        );

        await user.save();

        await user.populate('cart.shoe', 'name brand price images');

        res.json(user.cart);
    } catch (error) {
        console.error('Remove from cart error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Clear entire cart
// @route   DELETE /api/cart
// @access  Private
const clearCart = async (req, res) => {
    try {
        const user = await User.findById(req.user._id);
        user.cart = [];
        await user.save();

        res.json({ message: 'Cart cleared successfully' });
    } catch (error) {
        console.error('Clear cart error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

module.exports = {
    getCart,
    addToCart,
    updateCartItem,
    removeFromCart,
    clearCart
};
