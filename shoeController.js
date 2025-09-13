const Shoe = require('../models/Shoe');
const { validationResult } = require('express-validator');

// @desc    Get all shoes with filters
// @route   GET /api/shoes
// @access  Public
const getShoes = async (req, res) => {
    try {
        const {
            page = 1,
            limit = 12,
            category,
            gender,
            condition,
            minPrice,
            maxPrice,
            brand,
            search,
            sortBy = 'createdAt',
            sortOrder = 'desc',
            shop
        } = req.query;

        // Build filter object
        const filter = { isActive: true };

        if (category) filter.category = category;
        if (gender) filter.gender = gender;
        if (condition) filter.condition = condition;
        if (minPrice || maxPrice) {
            filter.price = {};
            if (minPrice) filter.price.$gte = Number(minPrice);
            if (maxPrice) filter.price.$lte = Number(maxPrice);
        }
        if (brand) filter.brand = new RegExp(brand, 'i');
        if (search) {
            filter.$text = { $search: search };
        }
        if (shop === 'true' && req.user) {
            filter.shop = req.user._id;
        }

        // Build sort object
        const sort = {};
        sort[sortBy] = sortOrder === 'desc' ? -1 : 1;

        const shoes = await Shoe.find(filter)
            .populate('shop', 'name shopName shopLogo rating')
            .sort(sort)
            .limit(limit * 1)
            .skip((page - 1) * limit)
            .exec();

        const total = await Shoe.countDocuments(filter);

        res.json({
            shoes,
            totalPages: Math.ceil(total / limit),
            currentPage: page,
            total
        });
    } catch (error) {
        console.error('Get shoes error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get single shoe
// @route   GET /api/shoes/:id
// @access  Public
const getShoeById = async (req, res) => {
    try {
        const shoe = await Shoe.findById(req.params.id)
            .populate('shop', 'name shopName shopLogo rating');

        if (!shoe) {
            return res.status(404).json({ message: 'Shoe not found' });
        }

        res.json(shoe);
    } catch (error) {
        console.error('Get shoe error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Create new shoe
// @route   POST /api/shoes
// @access  Private (Shop)
const createShoe = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const shoeData = {
            ...req.body,
            shop: req.user._id
        };

        const shoe = new Shoe(shoeData);
        await shoe.save();

        await shoe.populate('shop', 'name shopName shopLogo rating');

        res.status(201).json(shoe);
    } catch (error) {
        console.error('Create shoe error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Update shoe
// @route   PUT /api/shoes/:id
// @access  Private (Shop)
const updateShoe = async (req, res) => {
    try {
        const shoe = await Shoe.findById(req.params.id);

        if (!shoe) {
            return res.status(404).json({ message: 'Shoe not found' });
        }

        // Check if user owns this shoe or is admin
        if (shoe.shop.toString() !== req.user._id.toString() && req.user.role !== 'admin') {
            return res.status(403).json({ message: 'Not authorized' });
        }

        const updatedShoe = await Shoe.findByIdAndUpdate(
            req.params.id,
            req.body,
            { new: true }
        ).populate('shop', 'name shopName shopLogo rating');

        res.json(updatedShoe);
    } catch (error) {
        console.error('Update shoe error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Delete shoe
// @route   DELETE /api/shoes/:id
// @access  Private (Shop)
const deleteShoe = async (req, res) => {
    try {
        const shoe = await Shoe.findById(req.params.id);

        if (!shoe) {
            return res.status(404).json({ message: 'Shoe not found' });
        }

        // Check if user owns this shoe or is admin
        if (shoe.shop.toString() !== req.user._id.toString() && req.user.role !== 'admin') {
            return res.status(403).json({ message: 'Not authorized' });
        }

        await Shoe.findByIdAndDelete(req.params.id);

        res.json({ message: 'Shoe deleted successfully' });
    } catch (error) {
        console.error('Delete shoe error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get all categories
// @route   GET /api/shoes/categories
// @access  Public
const getCategories = async (req, res) => {
    try {
        const categories = await Shoe.distinct('category');
        res.json(categories);
    } catch (error) {
        console.error('Get categories error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get all brands
// @route   GET /api/shoes/brands
// @access  Public
const getBrands = async (req, res) => {
    try {
        const brands = await Shoe.distinct('brand');
        res.json(brands);
    } catch (error) {
        console.error('Get brands error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get shop statistics computed from orders
// @route   GET /api/shoes/shop-stats
// @access  Private (Shop)
const getShopStats = async (req, res) => {
    try {
        const shopId = req.user._id;

        const totalProducts = await Shoe.countDocuments({ shop: shopId });

        // Compute stats from orders that include items from this shop
        const Order = require('../models/Order');
        const sinceMonthStart = new Date();
        sinceMonthStart.setDate(1);
        sinceMonthStart.setHours(0, 0, 0, 0);

        const orders = await Order.find({ 'items': { $elemMatch: {} } })
            .populate('items.shoe', 'shop price');

        let totalSales = 0;
        let monthlySales = 0;
        const customersSet = new Set();

        for (const order of orders) {
            let orderHasShopItem = false;
            let orderTotalForShop = 0;
            for (const item of order.items) {
                if (item.shoe && item.shoe.shop && item.shoe.shop.toString() === shopId.toString()) {
                    orderHasShopItem = true;
                    orderTotalForShop += item.price * item.quantity;
                }
            }
            if (orderHasShopItem) {
                totalSales += orderTotalForShop;
                if (order.createdAt >= sinceMonthStart) {
                    monthlySales += orderTotalForShop;
                }
                customersSet.add(order.user.toString());
            }
        }

        res.json({
            totalProducts,
            totalSales,
            monthlySales,
            customers: customersSet.size
        });
    } catch (error) {
        console.error('Get shop stats error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

module.exports = {
    getShoes,
    getShoeById,
    createShoe,
    updateShoe,
    deleteShoe,
    getCategories,
    getBrands,
    getShopStats,
    // Return all shoes owned by the authenticated shop
    async getMyShoes(req, res) {
        try {
            const shopId = req.user._id;
            const shoes = await Shoe.find({ shop: shopId })
                .populate('shop', 'name shopName shopLogo rating')
                .sort({ createdAt: -1 });
            res.json({ shoes });
        } catch (error) {
            console.error('Get my shoes error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    },
    // Add or update a review for a shoe by the current user
    async addShoeReview(req, res) {
        try {
            const { rating, comment } = req.body;
            if (!rating || rating < 1 || rating > 5) {
                return res.status(400).json({ message: 'Rating must be between 1 and 5' });
            }

            const shoe = await Shoe.findById(req.params.id);
            if (!shoe) return res.status(404).json({ message: 'Shoe not found' });

            // Initialize reviews array if missing (older docs)
            if (!Array.isArray(shoe.reviews)) {
                shoe.reviews = [];
            }

            // Check if user already reviewed
            const existingIndex = shoe.reviews.findIndex(r => r.user.toString() === req.user._id.toString());
            if (existingIndex >= 0) {
                // Update existing review
                shoe.reviews[existingIndex].rating = rating;
                shoe.reviews[existingIndex].comment = comment || '';
                shoe.reviews[existingIndex].updatedAt = new Date();
            } else {
                shoe.reviews.push({ user: req.user._id, rating, comment: comment || '' });
            }

            // Recompute rating and reviewCount
            const { sum, count } = shoe.reviews.reduce((acc, r) => ({ sum: acc.sum + Number(r.rating || 0), count: acc.count + 1 }), { sum: 0, count: 0 });
            shoe.reviewCount = count;
            shoe.rating = count > 0 ? (sum / count) : 0;

            await shoe.save();
            await shoe.populate('reviews.user', 'name');
            res.json({ message: 'Review saved', rating: shoe.rating, reviewCount: shoe.reviewCount, reviews: shoe.reviews });
        } catch (error) {
            console.error('Add shoe review error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    }
};
