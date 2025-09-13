const mongoose = require('mongoose');

const customizationTemplateSchema = new mongoose.Schema({
    name: {
        type: String,
        required: true,
        trim: true
    },
    description: {
        type: String,
        required: true
    },
    basePrice: {
        type: Number,
        required: true,
        min: 0
    },
    category: {
        type: String,
        required: true,
        enum: ['sneakers', 'formal', 'casual', 'sports', 'boots', 'sandals']
    },
    image: {
        type: String,
        required: true
    },
    customizableParts: [{
        name: {
            type: String,
            required: true
        },
        type: {
            type: String,
            required: true,
            enum: ['body', 'sole', 'laces', 'heel', 'toe']
        },
        defaultColor: {
            type: String,
            required: true
        },
        availableColors: [{
            name: String,
            hex: String,
            priceModifier: {
                type: Number,
                default: 0
            }
        }],
        styles: [{
            name: String,
            priceModifier: {
                type: Number,
                default: 0
            }
        }]
    }],
    isActive: {
        type: Boolean,
        default: true
    }
}, {
    timestamps: true
});

// Index for better search performance
customizationTemplateSchema.index({ category: 1, isActive: 1 });

module.exports = mongoose.model('CustomizationTemplate', customizationTemplateSchema);

