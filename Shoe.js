const mongoose = require('mongoose');

const shoeSchema = new mongoose.Schema({
  name: {
    type: String,
    required: true,
    trim: true
  },
  brand: {
    type: String,
    required: true,
    trim: true
  },
  description: {
    type: String,
    required: true
  },
  price: {
    type: Number,
    required: true,
    min: 0
  },
  originalPrice: {
    type: Number,
    min: 0
  },
  images: [{
    type: String,
    required: true
  }],
  category: {
    type: String,
    required: true,
    enum: ['sneakers', 'formal', 'casual', 'sports', 'boots', 'sandals']
  },
  gender: {
    type: String,
    required: true,
    enum: ['men', 'women', 'unisex']
  },
  condition: {
    type: String,
    required: true,
    enum: ['new', 'used', 'refurbished']
  },
  sizes: [{
    size: {
      type: String,
      required: true
    },
    stock: {
      type: Number,
      required: true,
      min: 0
    }
  }],
  colors: [{
    name: {
      type: String,
      required: true
    },
    hex: {
      type: String,
      required: true
    }
  }],
  shop: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  rating: {
    type: Number,
    default: 0,
    min: 0,
    max: 5
  },
  reviewCount: {
    type: Number,
    default: 0
  },
  reviews: [{
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    rating: { type: Number, min: 1, max: 5, required: true },
    comment: { type: String, default: '' },
    createdAt: { type: Date, default: Date.now },
    updatedAt: { type: Date, default: Date.now }
  }],
  isCustomizable: {
    type: Boolean,
    default: false
  },
  customizationOptions: {
    laces: [{
      name: String,
      color: String,
      price: Number
    }],
    sole: [{
      name: String,
      color: String,
      price: Number
    }],
    body: [{
      name: String,
      color: String,
      price: Number
    }]
  },
  tags: [String],
  isActive: {
    type: Boolean,
    default: true
  }
}, {
  timestamps: true
});

// Index for better search performance
shoeSchema.index({ name: 'text', brand: 'text', description: 'text' });
shoeSchema.index({ category: 1, gender: 1, condition: 1 });
shoeSchema.index({ price: 1 });

module.exports = mongoose.model('Shoe', shoeSchema);
