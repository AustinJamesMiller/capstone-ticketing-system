#!/usr/bin/env python3

#Stack our imports here
import sys
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sqlalchemy import create_engine
from sqlalchemy import text
from scipy import sparse
import joblib

# The database URL must be in a specific format
db_url = "mysql+mysqlconnector://{USER}:{PWD}@{HOST}/{DBNAME}"
# Replace the values below with your own
# DB username, password, host and database name
db_url = db_url.format(
    USER = "root",
    PWD = "Passw0rd",
    HOST = "localhost",
    DBNAME = "phpticket"
)
# Create the DB engine instance. We'll use
# this engine to connect to the database
engine = create_engine(db_url)

x = sys.argv[1:100]

if 'kba' in x:
    post = "kba"
    x = " ".join(x).split("kba")
    x = [a.strip() for a in x]

    sql_query = text("select id, content_clean, device from potato")
    with engine.begin() as conn:
        data = pd.read_sql_query(
            sql_query,con=conn)
else:
    post = ""
    x = " ".join(x).split(" tickets ")
    
    sql_query = text("select ticket_id, email, subject from tickets")
    
    with engine.begin() as conn:
            data = pd.read_sql_query(
                sql_query,con=conn)

#Initialize the vectorizer
device_vectorizer = joblib.load('device.pkl')
content_vectorizer = joblib.load('content.pkl')
ticket_vectorizer = joblib.load('subject.pkl')

#---------------

tid = []
tida = []
data = data.replace('\n','', regex=True)
data = data.replace('\r','', regex=True)

if post == "kba":
    if "" not in x:
        #Find tickets that used the product described in the ticket, or similar products
        X = sparse.load_npz("device.npz")
        
        vect = device_vectorizer.transform([x[0]])
        results = cosine_similarity(X,vect).reshape((-1,))
        
        #this list will hold the ticket id's that match
        for i in results.argsort()[-10:][::-1]:
            if results[i] > 0.5: #read from database/configuration
                tid.append(data.iloc[i,0])

        Y = sparse.load_npz("content.npz")
        
        vecta = content_vectorizer.transform([x[1]])
        resultsa = cosine_similarity(Y,vecta).reshape((-1,))

        #this list will hold the ticket id's that 

        for i in resultsa.argsort()[-10:][::-1]:
            if resultsa[i] > 0.5:
                tida.append(data.iloc[i,0])
        
        print(set(tid) & set(tida))

    #else if statement to handle kba.php if statement 2 (when the device field was left empty, but search was populated).
    elif x[0] == "":
        #Find tickets that used the product described in the ticket, or similar products
        Y = sparse.load_npz("content.npz")
        
        vecta = content_vectorizer.transform([x[1]])
        resultsa = cosine_similarity(Y,vecta).reshape((-1,))

        #this list will hold the ticket id's that 

        for i in resultsa.argsort()[-10:][::-1]:
            if resultsa[i] > 0.5:
                tida.append(data.iloc[i,0])

        print(tida)

    elif x[1] == "":
        #Find tickets that used the product described in the ticket, or similar products
        X = sparse.load_npz("device.npz")
        
        vect = device_vectorizer.transform([x[0]])
        results = cosine_similarity(X,vect).reshape((-1,))
        
        #this list will hold the ticket id's that 
        for i in results.argsort()[-10:][::-1]:
            if results[i] > 0.5: #read from database/configuration
                tid.append(data.iloc[i,0])
        
        print(tid)

else:
    #Find tickets that used the product described in the ticket, or similar products
    Z = sparse.load_npz("subject.npz")
    
    vect = ticket_vectorizer.transform([x[0]])
    results = cosine_similarity(Z,vect).reshape((-1,))
    
    #this list will hold the ticket id's that match
    for i in results.argsort()[-10:][::-1]:
        if results[i] > 0.3: #read from database/configuration
            tid.append(data.iloc[i,0])

    print(tid)

