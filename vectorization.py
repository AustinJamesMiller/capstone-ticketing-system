#! /usr/bin/env python3
#Stack our imports here
import sys
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sqlalchemy import create_engine
from sqlalchemy import text
from scipy import sparse
import joblib

x = sys.argv[0]

# The database URL must be in a specific format
db_url = "mysql+mysqlconnector://{USER}:{PWD}@{HOST}/{DBNAME}"

db_url = db_url.format(
    USER = "root",
    PWD = "Passw0rd",
    HOST = "localhost",
    DBNAME = "ticketing_system"
)
# Create the DB engine instance. We'll use
# this engine to connect to the database
engine = create_engine(db_url)

sql_query = text("select id, content_clean, device from articles")
sql_query_ticket = text("select ticket_id, subject from tickets")

# Execute the query and store result in
# the DataFrame 'data'
with engine.begin() as conn:
    data = pd.read_sql_query(
        sql=sql_query,
        con=conn
    )
    data_ticket = pd.read_sql_query(
        sql=sql_query_ticket,
        con=conn
    )

tfidf_vectorizer = TfidfVectorizer()

tid = []
tida = []
data = data.replace('\n','', regex=True)
data = data.replace('\r','', regex=True)

if "article" in x:
    X = tfidf_vectorizer.fit_transform(data['device'])
    joblib.dump(tfidf_vectorizer,'device.pkl')
    sparse.save_npz("device.npz", X, False)
    Y = tfidf_vectorizer.fit_transform(data['content_clean'])
    joblib.dump(tfidf_vectorizer,'content.pkl')
    sparse.save_npz("content.npz", Y, False)
else:
    Z = tfidf_vectorizer.fit_transform(data_ticket['subject'])
    joblib.dump(tfidf_vectorizer,'subject.pkl')
    sparse.save_npz("subject.npz", Z, False)
