from fastapi import FastAPI, Depends, HTTPException, status, Header
from fastapi.middleware.cors import CORSMiddleware
import hashlib
import datetime
from typing import List, Optional
from pydantic import BaseModel

import database
from database import get_db, get_next_sequence_value

# Initialize Database tables/indexes
database.init_db()

app = FastAPI(title="CUSAT Store API")

# Enable CORS for frontend compatibility
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Helper function to hash passwords
def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode()).hexdigest()

# Pydantic Schemas
class UserRegister(BaseModel):
    name: str
    email: str
    password: str

class UserLogin(BaseModel):
    email: str
    password: str

class ProductCreate(BaseModel):
    name: str
    price: float
    category: str
    description: str
    image_url: Optional[str] = None

class CartItemInput(BaseModel):
    product_id: int
    quantity: int

class OrderCreate(BaseModel):
    user_id: Optional[int] = None
    customer_name: str
    customer_email: str
    customer_phone: str
    department: str
    roll_number: str
    delivery_address: str
    items: List[CartItemInput]

class OrderStatusUpdate(BaseModel):
    status: str

# Seed dynamic initial mock products if DB is empty
def seed_products(db):
    if db.products.count_documents({}) == 0:
        initial_products = [
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "CUSAT Premium Hoodie",
                "price": 850.00,
                "category": "Apparel",
                "description": "Navy blue hoodie with the official CUSAT crest printed in white and gold. Standard fit.",
                "image_url": "https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "CUSAT Crest Ceramic Mug",
                "price": 220.00,
                "category": "Stationery",
                "description": "High-quality ceramic mug with gold detailing of the Cochin University crest. Dishwasher safe.",
                "image_url": "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Engineering Physics Textbook",
                "price": 520.00,
                "category": "Textbooks",
                "description": "Prescribed textbook for CUSAT B.Tech first-year syllabus. Fully updated edition.",
                "image_url": "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Lab Coat (White Cotton)",
                "price": 350.00,
                "category": "Apparel",
                "description": "Full-sleeve protective white lab coat made of breathable cotton blend. Required for Chemistry & Physics labs.",
                "image_url": "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Maker Kit (Arduino Uno & Sensors)",
                "price": 1250.00,
                "category": "Tech",
                "description": "Starter electronics kit containing an Arduino Uno board, breadboard, jumper wires, LEDs, and standard sensors.",
                "image_url": "https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "A2 Drawing Board & T-Square",
                "price": 950.00,
                "category": "Stationery",
                "description": "Durable wooden engineering drawing board along with a precise 60cm T-Square rule. Essential for Engineering Graphics.",
                "image_url": "https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=400"
            }
        ]
        db.products.insert_many(initial_products)
