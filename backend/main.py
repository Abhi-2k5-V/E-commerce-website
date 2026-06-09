from fastapi import FastAPI, Depends, HTTPException, status, Header
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
import hashlib
from typing import List, Optional
from pydantic import BaseModel

import database
from database import get_db, User, Product, Order, OrderItem

# Initialize Database tables
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

# Seed dynamic initial mock products if DB is empty
def seed_products(db: Session):
    if db.query(Product).count() == 0:
        initial_products = [
            Product(
                name="CUSAT Premium Hoodie",
                price=850.00,
                category="Apparel",
                description="Navy blue hoodie with the official CUSAT crest printed in white and gold. Standard fit.",
                image_url="https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=400"
            ),
            Product(
                name="CUSAT Crest Ceramic Mug",
                price=220.00,
                category="Stationery",
                description="High-quality ceramic mug with gold detailing of the Cochin University crest. Dishwasher safe.",
                image_url="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&q=80&w=400"
            ),
            Product(
                name="Engineering Physics Textbook",
                price=520.00,
                category="Textbooks",
                description="Prescribed textbook for CUSAT B.Tech first-year syllabus. Fully updated edition.",
                image_url="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=400"
            ),
            Product(
                name="Lab Coat (White Cotton)",
                price=350.00,
                category="Apparel",
                description="Full-sleeve protective white lab coat made of breathable cotton blend. Required for Chemistry & Physics labs.",
                image_url="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=400"
            ),
            Product(
                name="Maker Kit (Arduino Uno & Sensors)",
                price=1250.00,
                category="Tech",
                description="Starter electronics kit containing an Arduino Uno board, breadboard, jumper wires, LEDs, and standard sensors.",
                image_url="https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&q=80&w=400"
            ),
            Product(
                name="A2 Drawing Board & T-Square",
                price=950.00,
                category="Stationery",
                description="Durable wooden engineering drawing board along with a precise 60cm T-Square rule. Essential for Engineering Graphics.",
                image_url="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=400"
            )
        ]
        db.add_all(initial_products)
        db.commit()

# --- ROUTES ---

@app.on_event("startup")
def on_startup():
    db = next(get_db())
    seed_products(db)

@app.post("/api/register")
def register(user: UserRegister, db: Session = Depends(get_db)):
    # Check if user already exists
    db_user = db.query(User).filter(User.email == user.email).first()
    if db_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    # Check if this email should automatically be an admin
    # (Or keep it simple: admin@cusat.ac.in is admin)
    is_admin = False
    if user.email.lower() == "admin@cusat.ac.in":
        is_admin = True
        
    new_user = User(
        name=user.name,
        email=user.email,
        password_hash=hash_password(user.password),
        is_admin=is_admin
    )
    db.add(new_user)
    db.commit()
    db.refresh(new_user)
    return {
        "id": new_user.id,
        "name": new_user.name,
        "email": new_user.email,
        "is_admin": new_user.is_admin
    }

@app.post("/api/login")
def login(user: UserLogin, db: Session = Depends(get_db)):
    db_user = db.query(User).filter(User.email == user.email).first()
    if not db_user or db_user.password_hash != hash_password(user.password):
        raise HTTPException(status_code=400, detail="Invalid email or password")
    
    return {
        "id": db_user.id,
        "name": db_user.name,
        "email": db_user.email,
        "is_admin": db_user.is_admin
    }

@app.get("/api/products")
def get_products(category: Optional[str] = None, db: Session = Depends(get_db)):
    query = db.query(Product)
    if category and category != "All":
        query = query.filter(Product.category == category)
    return query.all()

@app.post("/api/products")
def create_product(product: ProductCreate, x_admin_token: Optional[str] = Header(None), db: Session = Depends(get_db)):
    # Simulating simple admin authorization via custom headers
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    new_product = Product(
        name=product.name,
        price=product.price,
        category=product.category,
        description=product.description,
        image_url=product.image_url or "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=400"
    )
    db.add(new_product)
    db.commit()
    db.refresh(new_product)
    return new_product

@app.delete("/api/products/{product_id}")
def delete_product(product_id: int, x_admin_token: Optional[str] = Header(None), db: Session = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    product = db.query(Product).filter(Product.id == product_id).first()
    if not product:
        raise HTTPException(status_code=404, detail="Product not found")
        
    db.delete(product)
    db.commit()
    return {"message": "Product deleted successfully"}

@app.post("/api/orders")
def create_order(order_data: OrderCreate, db: Session = Depends(get_db)):
    if not order_data.items:
        raise HTTPException(status_code=400, detail="Cart is empty")
        
    total_amount = 0.0
    order_items_to_create = []
    
    # Validate items and calculate total amount
    for item in order_data.items:
        product = db.query(Product).filter(Product.id == item.product_id).first()
        if not product:
            raise HTTPException(status_code=400, detail=f"Product with ID {item.product_id} not found")
        
        item_total = product.price * item.quantity
        total_amount += item_total
        
        order_items_to_create.append(
            OrderItem(
                product_id=product.id,
                product_name=product.name,
                quantity=item.quantity,
                price=product.price
            )
        )
        
    # Create the order
    new_order = Order(
        user_id=order_data.user_id,
        customer_name=order_data.customer_name,
        customer_email=order_data.customer_email,
        customer_phone=order_data.customer_phone,
        department=order_data.department,
        roll_number=order_data.roll_number,
        delivery_address=order_data.delivery_address,
        total_amount=total_amount,
        status="Pending"
    )
    
    # Associate items
    new_order.items = order_items_to_create
    
    db.add(new_order)
    db.commit()
    db.refresh(new_order)
    
    return {
        "id": new_order.id,
        "customer_name": new_order.customer_name,
        "total_amount": new_order.total_amount,
        "status": new_order.status,
        "created_at": new_order.created_at.strftime("%Y-%m-%d %H:%M:%S")
    }

@app.get("/api/orders")
def get_orders(x_admin_token: Optional[str] = Header(None), db: Session = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    orders = db.query(Order).order_by(Order.created_at.desc()).all()
    result = []
    for order in orders:
        items = []
        for item in order.items:
            items.append({
                "product_name": item.product_name,
                "quantity": item.quantity,
                "price": item.price
            })
        result.append({
            "id": order.id,
            "user_id": order.user_id,
            "customer_name": order.customer_name,
            "customer_email": order.customer_email,
            "customer_phone": order.customer_phone,
            "department": order.department,
            "roll_number": order.roll_number,
            "delivery_address": order.delivery_address,
            "total_amount": order.total_amount,
            "status": order.status,
            "created_at": order.created_at.strftime("%Y-%m-%d %H:%M:%S"),
            "items": items
        })
    return result

@app.get("/api/orders/user/{user_id}")
def get_user_orders(user_id: int, db: Session = Depends(get_db)):
    orders = db.query(Order).filter(Order.user_id == user_id).order_by(Order.created_at.desc()).all()
    result = []
    for order in orders:
        items = []
        for item in order.items:
            items.append({
                "product_name": item.product_name,
                "quantity": item.quantity,
                "price": item.price
            })
        result.append({
            "id": order.id,
            "total_amount": order.total_amount,
            "status": order.status,
            "created_at": order.created_at.strftime("%Y-%m-%d %H:%M:%S"),
            "items": items
        })
    return result
