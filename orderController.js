const Order = require('../models/Order');
const User = require('../models/User');
const Shoe = require('../models/Shoe');
const { validationResult } = require('express-validator');
const PDFDocument = require('pdfkit');

// @desc    Create new order
// @route   POST /api/orders
// @access  Private
const createOrder = async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { items, shippingAddress, paymentMethod, shippingZone, useProfileAddress } = req.body;
        let finalAddress = shippingAddress;
        if (useProfileAddress) {
            const profile = await User.findById(req.user._id).select('address');
            if (!profile || !profile.address || !profile.address.street) {
                return res.status(400).json({ message: 'Profile address is incomplete' });
            }
            finalAddress = profile.address;
        }
        const user = await User.findById(req.user._id).populate('cart.shoe');

        // Calculate total amount
        let totalAmount = 0;
        const orderItems = [];

        for (const item of items) {
            const shoe = await Shoe.findById(item.shoeId);
            if (!shoe || !shoe.isActive) {
                return res.status(400).json({ message: `Shoe ${item.shoeId} not found` });
            }

            const sizeStock = shoe.sizes.find(s => s.size === item.size);
            if (!sizeStock || sizeStock.stock < item.quantity) {
                return res.status(400).json({ message: `Insufficient stock for ${shoe.name} size ${item.size}` });
            }

            const itemTotal = shoe.price * item.quantity;
            totalAmount += itemTotal;

            orderItems.push({
                shoe: shoe._id,
                quantity: item.quantity,
                size: item.size,
                price: shoe.price,
                customization: item.customization || {}
            });
        }

        // Shipping cost: inside Dhaka = 60, outside = 100
        let shippingCost = 100;
        if (shippingZone === 'inside') shippingCost = 60;
        if (shippingZone === 'outside') shippingCost = 100;
        // Simple tax rule (optional)
        const tax = 0;
        const finalTotal = totalAmount + shippingCost + tax;

        const order = new Order({
            user: req.user._id,
            items: orderItems,
            shippingAddress: finalAddress,
            paymentMethod,
            totalAmount: finalTotal,
            shippingCost,
            tax
        });

        await order.save();

        // Update stock
        for (const item of orderItems) {
            const shoe = await Shoe.findById(item.shoe);
            const sizeIndex = shoe.sizes.findIndex(s => s.size === item.size);
            shoe.sizes[sizeIndex].stock -= item.quantity;
            await shoe.save();
        }

        // Clear user's cart
        user.cart = [];
        await user.save();

        await order.populate('items.shoe', 'name brand images');

        res.status(201).json(order);
    } catch (error) {
        console.error('Create order error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get user's orders
// @route   GET /api/orders
// @access  Private
const getOrders = async (req, res) => {
    try {
        const orders = await Order.find({ user: req.user._id })
            .populate({
                path: 'items.shoe',
                select: 'name brand images reviews rating reviewCount',
                populate: { path: 'reviews.user', select: 'name' }
            })
            .sort({ createdAt: -1 });

        res.json(orders);
    } catch (error) {
        console.error('Get orders error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get single order
// @route   GET /api/orders/:id
// @access  Private
const getOrderById = async (req, res) => {
    try {
        const order = await Order.findById(req.params.id)
            .populate('items.shoe', 'name brand images');

        if (!order) {
            return res.status(404).json({ message: 'Order not found' });
        }

        // Check if user owns this order
        if (order.user.toString() !== req.user._id.toString()) {
            return res.status(403).json({ message: 'Not authorized' });
        }

        res.json(order);
    } catch (error) {
        console.error('Get order error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

module.exports = {
    createOrder,
    getOrders,
    getOrderById,
    // Admin
    async adminListOrders(req, res) {
        try {
            const orders = await Order.find()
                .populate('user', 'name email')
                .populate('items.shoe', 'name brand images')
                .sort({ createdAt: -1 });
            res.json(orders);
        } catch (error) {
            console.error('Admin list orders error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    },
    async adminGetOrderById(req, res) {
        try {
            const order = await Order.findById(req.params.id)
                .populate('user', 'name email')
                .populate('items.shoe', 'name brand images');
            if (!order) return res.status(404).json({ message: 'Order not found' });
            res.json(order);
        } catch (error) {
            console.error('Admin get order error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    },
    async adminDownloadInvoice(req, res) {
        try {
            const order = await Order.findById(req.params.id)
                .populate('user', 'name email')
                .populate('items.shoe', 'name brand');
            if (!order) return res.status(404).json({ message: 'Order not found' });

            res.setHeader('Content-Type', 'application/pdf');
            res.setHeader('Content-Disposition', `attachment; filename=invoice-${order._id}.pdf`);

            const doc = new PDFDocument({ margin: 50 });
            doc.pipe(res);

            doc.fontSize(20).text('KicksLab Invoice', { align: 'center' });
            doc.moveDown();
            doc.fontSize(12).text(`Order ID: ${order._id}`);
            doc.text(`Date: ${new Date(order.createdAt).toLocaleString()}`);
            doc.text(`Customer: ${order.user?.name} (${order.user?.email})`);
            doc.moveDown();

            doc.fontSize(14).text('Items');
            doc.moveDown(0.5);
            order.items.forEach((item, idx) => {
                doc.fontSize(12).text(`${idx + 1}. ${item.shoe?.brand} ${item.shoe?.name} - Size ${item.size} x ${item.quantity} @ $${item.price.toFixed(2)}`);
            });
            doc.moveDown();
            doc.text(`Ship To:`);
            doc.text(`${order.shippingAddress?.street || ''}`);
            doc.text(`${order.shippingAddress?.city || ''}, ${order.shippingAddress?.state || ''} ${order.shippingAddress?.zipCode || ''}`);
            doc.text(`${order.shippingAddress?.country || 'BD'}`);
            doc.moveDown();
            const formatBDT = (n) => `৳${Number(n || 0).toFixed(2)}`;
            doc.text(`Subtotal: ${formatBDT(order.totalAmount - order.shippingCost - order.tax)}`);
            doc.text(`Shipping: ${formatBDT(order.shippingCost)}`);
            doc.text(`Tax: ${formatBDT(order.tax)}`);
            doc.text(`Total: ${formatBDT(order.totalAmount)}`, { underline: true });

            doc.end();
        } catch (error) {
            console.error('Admin download invoice error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    }
    ,
    async shopDownloadInvoice(req, res) {
        try {
            const order = await Order.findById(req.params.id)
                .populate('user', 'name email')
                .populate('items.shoe', 'name brand shop');
            if (!order) return res.status(404).json({ message: 'Order not found' });
            const ownsAny = order.items.some(i => i.shoe && i.shoe.shop && i.shoe.shop.toString() === req.user._id.toString());
            if (!ownsAny) return res.status(403).json({ message: 'Not authorized' });

            res.setHeader('Content-Type', 'application/pdf');
            res.setHeader('Content-Disposition', `attachment; filename=invoice-${order._id}.pdf`);
            const doc = new PDFDocument({ margin: 50 });
            doc.pipe(res);
            doc.fontSize(20).text('KicksLab Invoice', { align: 'center' });
            doc.moveDown();
            doc.fontSize(12).text(`Order ID: ${order._id}`);
            doc.text(`Date: ${new Date(order.createdAt).toLocaleString()}`);
            doc.text(`Customer: ${order.user?.name} (${order.user?.email})`);
            doc.moveDown();
            doc.fontSize(14).text('Items');
            doc.moveDown(0.5);
            order.items.forEach((item, idx) => {
                doc.fontSize(12).text(`${idx + 1}. ${item.shoe?.brand} ${item.shoe?.name} - Size ${item.size} x ${item.quantity} @ $${item.price.toFixed(2)}`);
            });
            doc.moveDown();
            doc.text(`Ship To:`);
            doc.text(`${order.shippingAddress?.street || ''}`);
            doc.text(`${order.shippingAddress?.city || ''}, ${order.shippingAddress?.state || ''} ${order.shippingAddress?.zipCode || ''}`);
            doc.text(`${order.shippingAddress?.country || 'BD'}`);
            doc.moveDown();
            const formatBDT2 = (n) => `৳${Number(n || 0).toFixed(2)}`;
            doc.text(`Shipping: ${formatBDT2(order.shippingCost)}`);
            doc.text(`Tax: ${formatBDT2(order.tax)}`);
            doc.text(`Total: ${formatBDT2(order.totalAmount)}`, { underline: true });
            doc.end();
        } catch (error) {
            console.error('Shop download invoice error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    },
    async shopListOrders(req, res) {
        try {
            const shopId = req.user._id;
            // Find shoes owned by this shop
            const shoes = await Shoe.find({ shop: shopId }).select('_id');
            const shoeIds = shoes.map(s => s._id);

            if (shoeIds.length === 0) {
                return res.json([]);
            }

            // Find orders that include any of these shoes
            const orders = await Order.find({ 'items.shoe': { $in: shoeIds } })
                .populate('user', 'name email')
                .populate('items.shoe', 'name brand images shop')
                .sort({ createdAt: -1 });

            // Transform orders to include only items that belong to this shop, plus a per-shop total
            const transformed = orders.map(order => {
                const shopItems = order.items.filter(i => i.shoe && i.shoe.shop && i.shoe.shop.toString() === shopId.toString());
                const shopTotal = shopItems.reduce((sum, i) => sum + (Number(i.price) * Number(i.quantity)), 0);
                return {
                    _id: order._id,
                    createdAt: order.createdAt,
                    orderStatus: order.orderStatus,
                    user: order.user,
                    shopItems,
                    shopTotal
                };
            });

            res.json(transformed);
        } catch (error) {
            console.error('Shop list orders error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    },
    async shopUpdateOrderStatus(req, res) {
        try {
            const { status } = req.body; // 'processing' | 'shipped' | 'delivered' | 'cancelled'
            const order = await Order.findById(req.params.id).populate('items.shoe', 'shop');
            if (!order) return res.status(404).json({ message: 'Order not found' });
            // Ensure this shop owns at least one item in order
            const ownsAny = order.items.some(i => i.shoe && i.shoe.shop && i.shoe.shop.toString() === req.user._id.toString());
            if (!ownsAny) return res.status(403).json({ message: 'Not authorized' });
            order.orderStatus = status;
            await order.save();
            res.json(order);
        } catch (error) {
            console.error('Shop update order status error:', error);
            res.status(500).json({ message: 'Server error' });
        }
    }
};
